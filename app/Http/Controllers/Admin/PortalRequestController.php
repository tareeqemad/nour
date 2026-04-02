<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessedPortalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PortalRequestController extends Controller
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->token   = config('services.portal.token', '');
        $this->baseUrl = rtrim(config('services.portal.base_url', 'https://e.services.gov.ps/api/ministry/'), '/') . '/';
    }

    /**
     * الهيدرز المطلوبة للـ API
     */
    private function headers(): array
    {
        return [
            'TOKEN'  => $this->token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * عرض صفحة الطلبات المقدمة أو AJAX
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->boolean('ajax')) {
            return $this->ajaxList($request);
        }

        return view('admin.portal-requests.index');
    }

    /**
     * AJAX – جلب قائمة الطلبات
     */
    private function ajaxList(Request $request): JsonResponse
    {
        $page        = max(1, (int) $request->query('page', 1));
        $perPage     = max(5, min(100, (int) $request->query('per_page', 15)));
        $status      = $request->filled('status') ? trim((string) $request->query('status')) : '';
        $date        = $request->filled('date') ? trim((string) $request->query('date')) : '';
        $appNo       = $request->filled('app_no') ? trim((string) $request->query('app_no')) : '';
        $applicantId = $request->filled('applicant_id') ? trim((string) $request->query('applicant_id')) : '';

        try {
            // ── 1. بحث برقم طلب محدد ──────────────────────────────────
            if ($appNo !== '') {
                $res = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->get($this->baseUrl . 'getApp/' . urlencode($appNo));

                if (!$res->successful()) {
                    return $this->errorResponse('خطأ في الاتصال: ' . $res->status());
                }

                $body = $res->json();

                if (!($body['status'] ?? false)) {
                    return $this->errorResponse('الطلب غير موجود');
                }

                // getApp يرجع {status, 0: {data, status, pureData, nextStatus, ...}}
                $appData = $body['0'] ?? $body['app'] ?? null;

                if (!$appData) {
                    return $this->errorResponse('لم يتم إيجاد بيانات الطلب');
                }

                $data       = $appData['data']     ?? [];
                $statusInfo = $appData['status']    ?? [];
                $pureData   = $appData['pureData']  ?? [];

                // استخراج رقم الهوية من pureData (قسم "بيانات مقدم الطلب")
                $applicantIdVal = $this->extractFromPureData($pureData, [
                    'رقم الهوية',
                    'رقم الهويه',
                    'رقم هوية مقدم الطلب',
                ]);

                // استخراج تاريخ التقديم من pureData (قسم "الإقرار بصحة البيانات")
                $insertedAt = $this->extractFromPureData($pureData, [
                    'تاريخ تقديم الطلب',
                    'تاريخ التقديم',
                    'تاريخ الطلب',
                ]);

                $row = [
                    'app_no'          => $appNo,
                    'ser_no'          => $data['serv_no'] ?? $data['ser_no'] ?? '',
                    'applicant_id'    => $applicantIdVal ?? '',
                    'app_status'      => $statusInfo['status'] ?? '',
                    'desc_app_status' => $statusInfo['desc']   ?? '',
                    'status_note'     => null,
                    'inserted_at'     => $this->normalizeDate($insertedAt),
                    'changed_at'      => null,
                    'pure_data'       => json_encode($pureData),
                ];

                // ── استدعاء تكميلي لجلب changed_at و status_note ──
                // getApp لا يحتوي عليهما، لكن getAppsByServiceIDAndIdNo يحتوي
                if (!empty($applicantIdVal)) {
                    try {
                        $supplementRes = Http::withHeaders($this->headers())
                            ->timeout(15)
                            ->get($this->baseUrl . 'getAppsByServiceIDAndIdNo/' . urlencode($applicantIdVal));

                        if ($supplementRes->successful()) {
                            $supplementBody = $supplementRes->json();
                            $supplementApps = $supplementBody['apps']['data'] ?? [];

                            foreach ($supplementApps as $sApp) {
                                if (($sApp['app_no'] ?? '') == $appNo) {
                                    $row['changed_at']  = $this->normalizeDate($sApp['changed_at'] ?? null);
                                    $row['status_note'] = $sApp['status_note'] ?? null;
                                    // تحديث inserted_at من المصدر الأدق إذا كان موجوداً
                                    if (!empty($sApp['inserted_at'])) {
                                        $row['inserted_at'] = $this->normalizeDate($sApp['inserted_at']);
                                    }
                                    break;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // لا نفشل الطلب الأصلي إذا فشل الاستدعاء التكميلي
                        Log::warning('Supplementary API call failed: ' . $e->getMessage());
                    }
                }

                return response()->json([
                    'ok'         => true,
                    'data'       => [$row],
                    'pagination' => ['allPages' => 1, 'currentPage' => 1],
                    'total'      => 1,
                ]);
            }

            // ── 2. بحث برقم هوية المواطن ─────────────────────────────
            if ($applicantId !== '') {
                $res = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->get($this->baseUrl . 'getAppsByServiceIDAndIdNo/' . urlencode($applicantId));

                return $this->parseListResponse($res, $page);
            }

            // ── 3. فلتر بالحالة ──────────────────────────────────────
            if ($status !== '') {
                $res = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->get($this->baseUrl . 'getAppsByStatus', [
                        'status'       => $status,
                        'page'         => $page,
                        'countPerPage' => $perPage,
                    ]);

                return $this->parseListResponse($res, $page);
            }

            // ── 4. فلتر بالتاريخ ─────────────────────────────────────
            if ($date !== '') {
                // تحويل من YYYY-MM-DD إلى DD-MM-YYYY
                $parts = explode('-', $date);
                $formatted = count($parts) === 3
                    ? "{$parts[2]}-{$parts[1]}-{$parts[0]}"
                    : $date;

                $res = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->get($this->baseUrl . 'getAppsByDate', ['date' => $formatted]);

                return $this->parseListResponse($res, $page);
            }

            // ── 5. الافتراضي: جميع الطلبات ───────────────────────────
            $res = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($this->baseUrl . 'getApps', [
                    'page'         => $page,
                    'countPerPage' => $perPage,
                ]);

            return $this->parseListResponse($res, $page);

        } catch (\Throwable $e) {
            Log::error('PortalRequestController@ajaxList: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء الاتصال بالخادم');
        }
    }

    /**
     * تحليل استجابة القائمة
     */
    private function parseListResponse(\Illuminate\Http\Client\Response $res, int $page): JsonResponse
    {
        if (!$res->successful()) {
            return $this->errorResponse('خطأ في الاتصال بالخادم: ' . $res->status());
        }

        $body = $res->json();

        if (!($body['status'] ?? false)) {
            $msg = $body['apps']['msg']['msgText'] ?? $body['msg']['msgText'] ?? 'حدث خطأ في الاستجابة';
            return $this->errorResponse($msg);
        }

        $apps       = $body['apps'] ?? [];
        $items      = $apps['data'] ?? [];
        $pagination = $apps['pagination'] ?? ['allPages' => 1, 'currentPage' => $page];

        // سجّل أسماء الحقول الحقيقية من أول عنصر
        if (!empty($items)) {
            $firstKeys = implode(', ', array_keys((array)$items[0]));
            $firstItem = $items[0];
            Log::info("parseListResponse raw keys: {$firstKeys}");
            Log::info('parseListResponse raw changed_at=' . json_encode($firstItem['changed_at'] ?? 'MISSING') .
                       ', status_note=' . json_encode($firstItem['status_note'] ?? 'MISSING'));
        }

        // تطبيع أسماء الحقول مع استخراج البيانات المفقودة من pureData
        $items = array_map(function (array $item): array {
            $pureDataRaw = $item['pure_data'] ?? $item['pureData'] ?? $item['PURE_DATA'] ?? null;

            // رقم الهوية: أولاً نبحث في الحقول المباشرة، وإن لم نجد نستخرج من pureData
            $applicantId = $this->findField($item, ['applicant_id', 'applicantId', 'APPLICANT_ID', 'id_no', 'ID_NO']);
            if (empty($applicantId) && !empty($pureDataRaw)) {
                $applicantId = $this->extractFromPureData($pureDataRaw, [
                    'رقم الهوية',
                    'رقم الهويه',
                    'رقم هوية مقدم الطلب',
                ]);
            }

            // تاريخ التقديم: نبحث في الحقول المباشرة (inserted_at أو app_date)
            $insertedAt = $this->findField($item, ['inserted_at', 'insertedAt', 'app_date', 'INSERT_DATE', 'insert_date', 'created_at']);
            if (empty($insertedAt) && !empty($pureDataRaw)) {
                $insertedAt = $this->extractFromPureData($pureDataRaw, [
                    'تاريخ تقديم الطلب',
                    'تاريخ التقديم',
                    'تاريخ الطلب',
                ]);
            }

            // آخر تعديل
            $changedAt = $this->findField($item, ['changed_at', 'changedAt', 'CHANGE_DATE', 'change_date', 'updated_at', 'last_update']);

            // ملاحظة الحالة
            $statusNote = $this->findField($item, ['status_note', 'statusNote', 'STATUS_NOTE', 'note', 'notes']);

            return [
                'app_no'          => $this->findField($item, ['app_no', 'appNo', 'APP_NO']) ?? '',
                'ser_no'          => $this->findField($item, ['ser_no', 'serNo', 'SER_NO', 'serv_no']) ?? '',
                'applicant_id'    => $applicantId ?? '',
                'app_status'      => $this->findField($item, ['app_status', 'appStatus', 'APP_STATUS', 'status']) ?? '',
                'desc_app_status' => $this->findField($item, ['desc_app_status', 'descAppStatus', 'DESC_APP_STATUS', 'status_desc', 'app_status_desc']) ?? '',
                'status_note'     => $statusNote,
                'inserted_at'     => $this->normalizeDate($insertedAt),
                'changed_at'      => $this->normalizeDate($changedAt),
                'pure_data'       => $pureDataRaw,
            ];
        }, array_values($items));

        // إضافة حالة الحساب (هل تم إنشاء يوزر لهذا الطلب)
        $appNos = array_column($items, 'app_no');
        $processedMap = [];
        if (!empty($appNos)) {
            $processedMap = ProcessedPortalRequest::whereIn('app_no', $appNos)
                ->get()
                ->keyBy('app_no');
        }

        foreach ($items as &$item) {
            $record = $processedMap[$item['app_no']] ?? null;
            $item['has_account'] = $record && $record->status === 'success';
            $item['account_info'] = $record ? [
                'status'       => $record->status,
                'username'     => $record->user ? $record->user->username : null,
                'user_id'      => $record->user_id,
                'operator_id'  => $record->operator_id,
                'processed_at' => $record->processed_at?->format('Y-m-d H:i'),
            ] : null;
        }
        unset($item);

        return response()->json([
            'ok'         => true,
            'data'       => $items,
            'pagination' => $pagination,
            'total'      => count($items),
            'msg'        => $apps['msg'] ?? null,
        ]);
    }

    /**
     * AJAX – جلب تفاصيل طلب واحد
     */
    public function show(Request $request, string $appId): JsonResponse
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($this->baseUrl . 'getApp/' . urlencode($appId));

            if (!$res->successful()) {
                return $this->errorResponse('خطأ في الاتصال: ' . $res->status());
            }

            $body = $res->json();

            if (!($body['status'] ?? false)) {
                return $this->errorResponse('الطلب غير موجود');
            }

            $appData = $body['0'] ?? $body['app'] ?? null;

            if (!$appData) {
                return $this->errorResponse('لم يتم إيجاد بيانات الطلب');
            }

            return response()->json([
                'ok'   => true,
                'data' => $appData,
            ]);

        } catch (\Throwable $e) {
            Log::error('PortalRequestController@show: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ');
        }
    }

    /**
     * AJAX – تغيير حالة الطلب
     * الـ API يستقبل GET مع newStatus و note كـ query params
     * الاستجابة الناجحة: { success: 1, msg: { msgNo: 1, msgText: "..." } }
     * الاستجابة الفاشلة: { success: 0, msg: { msgNo: 0, msgText: "..." } }
     */
    public function changeStatus(Request $request, string $appId): JsonResponse
    {
        $request->validate([
            'newStatus' => 'required|string|max:20',
            'note'      => 'nullable|string|max:1000',
        ]);

        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($this->baseUrl . 'changeStatus/' . urlencode($appId), [
                    'newStatus' => $request->input('newStatus'),
                    'note'      => $request->input('note', ''),
                ]);

            if (!$res->successful()) {
                return $this->errorResponse('خطأ في الاتصال بالخادم: ' . $res->status());
            }

            $body = $res->json();

            // الـ API يرجع success: 1 عند النجاح
            if (($body['success'] ?? 0) == 1) {
                return response()->json([
                    'ok'      => true,
                    'message' => $body['msg']['msgText'] ?? 'تم تغيير حالة الطلب بنجاح',
                ]);
            }

            $msg = $body['msg']['msgText'] ?? 'فشل تغيير الحالة';
            return $this->errorResponse($msg);

        } catch (\Throwable $e) {
            Log::error('PortalRequestController@changeStatus: ' . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء الاتصال');
        }
    }

    /**
     * البحث عن حقل في مصفوفة باستخدام قائمة أسماء محتملة
     */
    private function findField(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }
        }
        return null;
    }

    /**
     * استخراج قيمة حقل من بيانات pureData حسب اسم الحقل بالعربي
     *
     * pureData هو مصفوفة من الأقسام:
     * [{ "dt_name": "...", "dt_data": [[ { "fn": "اسم الحقل", "fv": ["القيمة"], "fp": [] }, ... ]] }]
     *
     * @param mixed  $pureData   JSON string أو مصفوفة أو null
     * @param array  $fieldNames أسماء الحقول المراد البحث عنها (بالعربي)
     * @return string|null
     */
    private function extractFromPureData(mixed $pureData, array $fieldNames): ?string
    {
        if (empty($pureData)) {
            return null;
        }

        // فك JSON إذا كان نصًا
        if (is_string($pureData)) {
            $pureData = json_decode($pureData, true);
        }

        if (!is_array($pureData)) {
            return null;
        }

        // البحث في كل الأقسام (sections) → صفوف (rows) → حقول (fields)
        foreach ($pureData as $section) {
            $dtData = $section['dt_data'] ?? [];
            foreach ($dtData as $row) {
                if (!is_array($row)) continue;
                foreach ($row as $field) {
                    if (!is_array($field)) continue;
                    $fn = $field['fn'] ?? '';
                    if (in_array($fn, $fieldNames, true)) {
                        $fv = $field['fv'] ?? [];
                        if (is_array($fv) && !empty($fv)) {
                            return $fv[0];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * توحيد صيغة التاريخ إلى YYYY-MM-DD HH:mm:ss
     *
     * الـ API يرجع التواريخ بصيغ مختلفة:
     * - "2026-03-02 21:24:03" (YYYY-MM-DD HH:mm:ss) من getAppsByServiceIDAndIdNo
     * - "02/03/2026" (DD/MM/YYYY) من getApps و pureData
     * - "02-03-2026" (DD-MM-YYYY) احتمال
     *
     * JavaScript new Date("02/03/2026") يفسرها كـ MM/DD/YYYY (أمريكي)
     * لذلك نحول الكل لصيغة YYYY-MM-DD في PHP
     */
    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        $date = trim($date);

        // صيغة YYYY-MM-DD (مع أو بدون وقت) - صحيحة مباشرة
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
            return $date;
        }

        // صيغة DD/MM/YYYY → تحويل لـ YYYY-MM-DD
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $date, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        // صيغة DD-MM-YYYY → تحويل لـ YYYY-MM-DD
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        return $date;
    }

    /**
     * AJAX – إنشاء حساب مستخدم من طلب بوابة محدد
     */
    public function createUser(Request $request, string $appId): JsonResponse
    {
        // التحقق من عدم المعالجة المسبقة
        if (ProcessedPortalRequest::isProcessed($appId)) {
            return response()->json([
                'ok'      => false,
                'message' => 'هذا الطلب تم إنشاء حساب له مسبقاً',
            ]);
        }

        try {
            $exitCode = Artisan::call('portal:process-approved', [
                '--app-no'       => $appId,
                '--processed-by' => $request->user()?->id,
            ]);

            $output = Artisan::output();

            if ($exitCode === 0) {
                $record = ProcessedPortalRequest::where('app_no', $appId)->first();
                $status = $record?->status ?? 'unknown';

                if ($status === 'success') {
                    return response()->json([
                        'ok'      => true,
                        'message' => 'تم إنشاء حساب المستخدم بنجاح',
                        'data'    => [
                            'user_id'     => $record->user_id,
                            'operator_id' => $record->operator_id,
                            'notes'       => $record->notes,
                        ],
                    ]);
                }

                return response()->json([
                    'ok'      => false,
                    'message' => $record?->notes ?? 'تم تخطي الطلب - ' . $output,
                ]);
            }

            return response()->json([
                'ok'      => false,
                'message' => 'فشل إنشاء الحساب: ' . trim($output),
            ]);
        } catch (\Throwable $e) {
            Log::error('PortalRequestController@createUser: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب',
            ]);
        }
    }

    /**
     * AJAX – التحقق من حالة معالجة طلب
     */
    public function checkProcessed(Request $request, string $appId): JsonResponse
    {
        $record = ProcessedPortalRequest::where('app_no', $appId)->first();

        return response()->json([
            'ok'        => true,
            'processed' => $record !== null && $record->status === 'success',
            'data'      => $record ? [
                'status'       => $record->status,
                'notes'        => $record->notes,
                'processed_at' => $record->processed_at?->format('Y-m-d H:i'),
                'user_id'      => $record->user_id,
                'operator_id'  => $record->operator_id,
            ] : null,
        ]);
    }

    /**
     * استجابة خطأ موحدة
     */
    private function errorResponse(string $message, int $httpCode = 200): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message], $httpCode);
    }
}
