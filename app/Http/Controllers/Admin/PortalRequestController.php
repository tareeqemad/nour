<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $status      = trim((string) $request->query('status', ''));
        $date        = trim((string) $request->query('date', ''));
        $appNo       = trim((string) $request->query('app_no', ''));
        $applicantId = trim((string) $request->query('applicant_id', ''));

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

                // نبني صف واحد من بيانات getApp
                $row = [
                    'app_no'          => $appNo,
                    'ser_no'          => $appData['data']['serv_no'] ?? '',
                    'applicant_id'    => '',
                    'app_status'      => $appData['status']['status'] ?? '',
                    'desc_app_status' => $appData['status']['desc'] ?? '',
                    'status_note'     => null,
                    'inserted_at'     => null,
                    'changed_at'      => null,
                    'pure_data'       => json_encode($appData['pureData'] ?? []),
                    '_from_single'    => true,
                ];

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
     * استجابة خطأ موحدة
     */
    private function errorResponse(string $message, int $httpCode = 200): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message], $httpCode);
    }
}
