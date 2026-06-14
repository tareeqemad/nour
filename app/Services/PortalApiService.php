<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * عميل لـ API بوابة الخدمات الفلسطينية (e.services.gov.ps).
 *
 * يجلب طلبات الانضمام ويُطبّع حقولها إلى الشكل المخزَّن محلياً في جدول portal_requests.
 * منطق التطبيع مطابق لما يستخدمه PortalRequestController في شاشة العرض.
 */
class PortalApiService
{
    private string $baseUrl;
    private string $token;
    private int $timeout;

    public function __construct()
    {
        $this->token   = (string) config('services.portal.token', '');
        $this->baseUrl = rtrim((string) config('services.portal.base_url', 'https://e.services.gov.ps/api/ministry/'), '/') . '/';
        $this->timeout = 60;
    }

    private function headers(): array
    {
        return [
            'TOKEN'  => $this->token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * جلب صفحة واحدة من الطلبات (مُطبّعة).
     *
     * @param array{status?: string, date?: string} $filters  (date بصيغة DD-MM-YYYY كما يطلبها الـ API)
     * @return array{items: array<int, array<string, mixed>>, allPages: int, currentPage: int}
     *
     * @throws \RuntimeException عند فشل الاتصال أو استجابة غير صالحة
     */
    public function fetchPage(int $page, int $perPage = 100, array $filters = []): array
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $date   = trim((string) ($filters['date'] ?? ''));

        if ($status !== '') {
            $res = Http::withHeaders($this->headers())->timeout($this->timeout)
                ->get($this->baseUrl . 'getAppsByStatus', [
                    'status'       => $status,
                    'page'         => $page,
                    'countPerPage' => $perPage,
                ]);
        } elseif ($date !== '') {
            $res = Http::withHeaders($this->headers())->timeout($this->timeout)
                ->get($this->baseUrl . 'getAppsByDate', ['date' => $date]);
        } else {
            $res = Http::withHeaders($this->headers())->timeout($this->timeout)
                ->get($this->baseUrl . 'getApps', [
                    'page'         => $page,
                    'countPerPage' => $perPage,
                ]);
        }

        if (!$res->successful()) {
            throw new \RuntimeException('فشل الاتصال بالـ API: HTTP ' . $res->status());
        }

        $body = $res->json();

        if (!is_array($body) || !($body['status'] ?? false)) {
            $msg = $body['apps']['msg']['msgText'] ?? $body['msg']['msgText'] ?? 'استجابة غير صالحة من الـ API';
            throw new \RuntimeException((string) $msg);
        }

        $apps       = $body['apps'] ?? [];
        $rawItems   = array_values($apps['data'] ?? []);
        $pagination = $apps['pagination'] ?? [];

        $items = array_map(fn ($item) => $this->normalizeItem((array) $item), $rawItems);

        return [
            'items'       => $items,
            'allPages'    => (int) ($pagination['allPages'] ?? 1),
            'currentPage' => (int) ($pagination['currentPage'] ?? $page),
        ];
    }

    /**
     * تطبيع عنصر طلب واحد إلى أعمدة جدول portal_requests.
     *
     * @return array<string, mixed>
     */
    public function normalizeItem(array $item): array
    {
        $pure = $this->toArray($item['pure_data'] ?? $item['pureData'] ?? $item['PURE_DATA'] ?? null);

        $applicantId = $this->findField($item, ['applicant_id', 'applicantId', 'APPLICANT_ID', 'id_no', 'ID_NO']);
        if (empty($applicantId) && $pure) {
            $applicantId = $this->extractFromPureData($pure, ['رقم الهوية', 'رقم الهويه', 'رقم هوية مقدم الطلب']);
        }

        $insertedAt = $this->findField($item, ['inserted_at', 'insertedAt', 'app_date', 'INSERT_DATE', 'insert_date', 'created_at']);
        if (empty($insertedAt) && $pure) {
            $insertedAt = $this->extractFromPureData($pure, ['تاريخ تقديم الطلب', 'تاريخ التقديم', 'تاريخ الطلب']);
        }

        $changedAt  = $this->findField($item, ['changed_at', 'changedAt', 'CHANGE_DATE', 'change_date', 'updated_at', 'last_update']);
        $statusNote = $this->findField($item, ['status_note', 'statusNote', 'STATUS_NOTE', 'note', 'notes']);
        $name       = $pure ? $this->extractFromPureData($pure, ['الاسم كاملاً', 'الاسم كاملا', 'الاسم الكامل', 'الاسم']) : null;

        return [
            'app_no'          => (string) ($this->findField($item, ['app_no', 'appNo', 'APP_NO']) ?? ''),
            'ser_no'          => $this->findField($item, ['ser_no', 'serNo', 'SER_NO', 'serv_no']),
            'applicant_id'    => $applicantId,
            'applicant_name'  => $name,
            'app_status'      => $this->findField($item, ['app_status', 'appStatus', 'APP_STATUS', 'status']),
            'desc_app_status' => $this->findField($item, ['desc_app_status', 'descAppStatus', 'DESC_APP_STATUS', 'status_desc', 'app_status_desc']),
            'status_note'     => $statusNote,
            'inserted_at'     => $this->normalizeDate($insertedAt),
            'changed_at'      => $this->normalizeDate($changedAt),
            'pure_data'       => $pure,
        ];
    }

    /**
     * البحث عن قيمة حقل ضمن عدة أسماء محتملة.
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
     * استخراج قيمة حقل عربي من بنية pureData (أقسام → صفوف → حقول).
     */
    private function extractFromPureData(?array $pureData, array $fieldNames): ?string
    {
        if (empty($pureData)) {
            return null;
        }

        foreach ($pureData as $section) {
            $dtData = $section['dt_data'] ?? [];
            foreach ($dtData as $row) {
                if (!is_array($row)) {
                    continue;
                }
                foreach ($row as $field) {
                    if (!is_array($field)) {
                        continue;
                    }
                    if (in_array($field['fn'] ?? '', $fieldNames, true)) {
                        $fv = $field['fv'] ?? [];
                        if (is_array($fv) && !empty($fv)) {
                            return (string) $fv[0];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * توحيد التاريخ إلى صيغة قابلة للتخزين (Y-m-d H:i:s) أو null إن تعذّر التحليل.
     * الـ API يرجع: YYYY-MM-DD[ HH:mm:ss] أو DD/MM/YYYY أو DD-MM-YYYY.
     */
    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        $date = trim($date);

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $date, $m)) {
            $date = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        } elseif (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $date, $m)) {
            $date = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * تحويل قيمة pure_data (نص JSON أو مصفوفة) إلى مصفوفة أو null.
     */
    private function toArray(mixed $value): ?array
    {
        if (empty($value)) {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }
}
