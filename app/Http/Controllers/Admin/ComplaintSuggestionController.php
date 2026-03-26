<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintSuggestion;
use App\Traits\SanitizesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintSuggestionController extends Controller
{
    use SanitizesInput;

    /**
     * عرض قائمة الشكاوى والمقترحات
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $operatorIds = $this->getOperatorScope($user);

        // بناء query أساسي مع scope المشغل
        $query = ComplaintSuggestion::query()
            ->with(['operator', 'generator', 'responder']);

        $this->applyOperatorScope($query, $user, $operatorIds);

        // تطبيق الفلاتر
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $this->sanitizeSearchInput($request->search);
            if (!empty($search)) {
                $query->where(fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('tracking_code', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%")
                );
            }
        }

        // إحصائيات بـ query واحد بدال 6
        $stats = $this->calculateStats($user, $operatorIds);

        $complaintsSuggestions = $query->orderByDesc('created_at')->paginate(100);

        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.complaints-suggestions.partials.tbody-rows', compact('complaintsSuggestions'))->render(),
                'pagination' => view('admin.complaints-suggestions.partials.pagination', compact('complaintsSuggestions'))->render(),
                'count' => $complaintsSuggestions->total(),
                'stats' => $stats,
            ]);
        }

        return view('admin.complaints-suggestions.index', compact('complaintsSuggestions', 'stats'));
    }

    /**
     * عرض تفاصيل شكوى/مقترح
     */
    public function show(ComplaintSuggestion $complaintSuggestion)
    {
        $this->authorizeAccess($complaintSuggestion);

        $complaintSuggestion->load([
            'operator',
            'generator' => fn ($q) => $q->withTrashed(),
            'responder',
        ]);

        return view('admin.complaints-suggestions.show', compact('complaintSuggestion'));
    }

    /**
     * عرض صفحة الرد/التعديل
     */
    public function edit(ComplaintSuggestion $complaintSuggestion)
    {
        $this->authorizeAccess($complaintSuggestion);

        $complaintSuggestion->load([
            'operator',
            'generator' => fn ($q) => $q->withTrashed(),
            'responder',
        ]);

        return view('admin.complaints-suggestions.edit', compact('complaintSuggestion'));
    }

    /**
     * الرد على شكوى/مقترح (يدمج update + respond)
     */
    public function respond(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $this->authorizeAccess($complaintSuggestion);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'response' => 'required|string|max:5000',
        ]);

        $complaintSuggestion->update([
            'status' => $validated['status'],
            'response' => $validated['response'],
            'responded_by' => Auth::id(),
            'responded_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم إرسال الرد بنجاح']);
        }

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم إرسال الرد بنجاح');
    }

    /**
     * تحديث الحالة فقط (بدون رد)
     */
    public function update(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'response' => 'nullable|string|max:5000',
        ]);

        $complaintSuggestion->update([
            'status' => $validated['status'],
            'response' => $validated['response'] ?? $complaintSuggestion->response,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث الطلب بنجاح']);
        }

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم تحديث الطلب بنجاح');
    }

    /**
     * حذف شكوى/مقترح
     */
    public function destroy(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $complaintSuggestion->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم حذف الطلب بنجاح']);
        }

        return redirect()->route('admin.complaints-suggestions.index')
            ->with('success', 'تم حذف الطلب بنجاح');
    }

    /**
     * إغلاق الشكوى من قبل المشغل
     */
    public function closeByOperator(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();

        if (!$user->isCompanyOwner()) {
            abort(403, 'غير مصرح لك بإغلاق هذه الشكوى');
        }

        $operatorIds = $user->ownedOperators()->pluck('id')->toArray();
        if (!$complaintSuggestion->operator_id || !in_array($complaintSuggestion->operator_id, $operatorIds)) {
            abort(403, 'هذه الشكوى غير مرتبطة بمشغلك');
        }

        $validated = $request->validate([
            'response' => 'nullable|string|max:5000',
            'status' => 'required|in:resolved,rejected',
        ]);

        $complaintSuggestion->update([
            'status' => $validated['status'],
            'response' => $validated['response'] ?? $complaintSuggestion->response,
            'responded_by' => $user->id,
            'responded_at' => now(),
            'closed_by_operator' => true,
            'closed_at' => now(),
        ]);

        // إرسال إشعارات
        \App\Models\Notification::notifyOperatorApprovers(
            'complaint_closed_by_operator',
            'تم إغلاق شكوى/مقترح',
            "تم إغلاق " . ($complaintSuggestion->type === 'complaint' ? 'شكوى' : 'مقترح') . " من قبل المشغل: {$complaintSuggestion->operator->name}",
            route('admin.complaints-suggestions.show', $complaintSuggestion)
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم إغلاق الشكوى بنجاح']);
        }

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم إغلاق الشكوى بنجاح');
    }

    // ══════════════════════════════════════════════
    // Private Helpers
    // ══════════════════════════════════════════════

    /**
     * جمع IDs المشغلين للمستخدم (محسوبة مرة وحدة)
     */
    private function getOperatorScope($user): array
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return []; // فارغ = كل شي (no scope)
        }

        $ids = [];

        if ($user->isCompanyOwner()) {
            $ids = array_merge($ids, $user->ownedOperators()->pluck('id')->toArray());
        }

        $ids = array_merge($ids, $user->operators()->pluck('operators.id')->toArray());

        if ($user->roleModel && $user->roleModel->operator_id) {
            $ids[] = $user->roleModel->operator_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * تطبيق scope المشغل على أي query
     */
    private function applyOperatorScope($query, $user, array $operatorIds): void
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return; // لا scope
        }

        if (empty($operatorIds)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('operator_id', $operatorIds);
        }
    }

    /**
     * التحقق من صلاحية الوصول
     */
    private function authorizeAccess(ComplaintSuggestion $item): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return;
        }

        if (!$item->operator_id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الشكوى');
        }

        $operatorIds = $this->getOperatorScope($user);
        if (!in_array($item->operator_id, $operatorIds)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الشكوى');
        }
    }

    /**
     * إحصائيات بـ query واحد (بدال 6 queries منفصلة)
     */
    private function calculateStats($user, array $operatorIds): array
    {
        $query = ComplaintSuggestion::query();
        $this->applyOperatorScope($query, $user, $operatorIds);

        $raw = $query->select(DB::raw("
            COUNT(*) as total,
            SUM(CASE WHEN type = 'complaint' THEN 1 ELSE 0 END) as complaints,
            SUM(CASE WHEN type = 'suggestion' THEN 1 ELSE 0 END) as suggestions,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
        "))->first();

        return [
            'total' => (int) $raw->total,
            'complaints' => (int) $raw->complaints,
            'suggestions' => (int) $raw->suggestions,
            'pending' => (int) $raw->pending,
            'in_progress' => (int) $raw->in_progress,
            'resolved' => (int) $raw->resolved,
        ];
    }
}
