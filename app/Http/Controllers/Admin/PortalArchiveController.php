<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * عرض الأرشيف المحلي لطلبات البوابة (جدول portal_requests).
 * تقرأ من قاعدة البيانات المحلية مباشرة — لا تتصل بالـ API.
 * تُملأ البيانات عبر الأمر: php artisan portal:sync-requests
 */
class PortalArchiveController extends Controller
{
    private function ensure(string $permission): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission($permission), 403, 'ليس لديك صلاحية لهذا الإجراء.');
    }

    /**
     * قائمة الطلبات المخزّنة محلياً (مع فلاتر + ترقيم صفحات من قاعدة البيانات).
     */
    public function index(Request $request): View
    {
        $this->ensure('portal_archive.view');

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $date   = trim((string) $request->query('date', ''));

        $perPage = (int) $request->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100, 200], true) ? $perPage : 50;

        $query = PortalRequest::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('app_no', 'like', "%{$search}%")
                  ->orWhere('applicant_id', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('app_status', $status);
        }

        if ($date !== '') {
            $query->whereDate('inserted_at', $date);
        }

        $requests = $query->orderByDesc('inserted_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total'     => PortalRequest::count(),
            'last_sync' => PortalRequest::max('synced_at'),
        ];

        return view('admin.portal-archive.index', compact('requests', 'stats', 'search', 'status', 'date', 'perPage'));
    }

    /**
     * تفاصيل طلب واحد من الأرشيف المحلي (JSON) — لعرضها في النافذة المنبثقة.
     */
    public function show(string $appNo): JsonResponse
    {
        $this->ensure('portal_archive.view');

        $record = PortalRequest::where('app_no', $appNo)->first();

        if (!$record) {
            return response()->json([
                'ok'      => false,
                'message' => 'الطلب غير موجود في الأرشيف المحلي',
            ]);
        }

        return response()->json([
            'ok'   => true,
            'data' => [
                'app_no'          => $record->app_no,
                'ser_no'          => $record->ser_no,
                'applicant_id'    => $record->applicant_id,
                'applicant_name'  => $record->applicant_name,
                'app_status'      => $record->app_status,
                'desc_app_status' => $record->desc_app_status,
                'status_note'     => $record->status_note,
                'inserted_at'     => $record->inserted_at?->format('Y-m-d H:i'),
                'changed_at'      => $record->changed_at?->format('Y-m-d H:i'),
                'synced_at'       => $record->synced_at?->format('Y-m-d H:i'),
                'pure_data'       => $record->pure_data,
            ],
        ]);
    }
}
