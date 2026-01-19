<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintSuggestionController extends Controller
{
    /**
     * عرض قائمة الشكاوى والمقترحات
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // بناء الاستعلام الأساسي
        $query = ComplaintSuggestion::query();
        
        // تحميل العلاقات (بما في ذلك المولدات المحذوفة soft delete)
        $query->with([
            'operator',
            'generator' => function($q) {
                $q->withTrashed(); // تضمين المولدات المحذوفة soft delete
            },
            'responder'
        ]);
        
        // تصفية حسب نوع المستخدم
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            // السوبر ادمن والادمن وسلطة الطاقة يشوفوا كل الشكاوى
        } else {
            // للمشغل والموظفين: فقط الشكاوى المرتبطة بمشغليهم
            $operatorIds = $this->getUserOperatorIds($user);
            
            if (empty($operatorIds)) {
                // لا يوجد مشغلين مرتبطين - إرجاع استعلام فارغ
                $query->whereRaw('1 = 0');
            } else {
                // البحث عن الشكاوى المرتبطة بهذه المشغلين
                $query->whereIn('operator_id', $operatorIds);
            }
        }

        // تطبيق الفلاتر
        // النوع: إذا كانت القيمة "all" لا نطبق فلتر، غير ذلك نطبق الشرط
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // الحالة: إذا كانت القيمة "all" لا نطبق فلتر، غير ذلك نطبق الشرط
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // البحث النصي
        if ($request->filled('search')) {
            $search = $this->sanitizeSearchInput($request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('tracking_code', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            }
        }

        // حساب الإحصائيات قبل pagination (بدون فلتر الحالة للـ stats)
        $statsQuery = ComplaintSuggestion::query();
        if (!($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority())) {
            $operatorIds = $this->getUserOperatorIds($user);
            if (!empty($operatorIds)) {
                $statsQuery->whereIn('operator_id', $operatorIds);
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        }
        $stats = $this->calculateStats($statsQuery);

        // الترتيب والـ pagination
        $complaintsSuggestions = $query->orderBy('created_at', 'desc')->paginate(100);

        // إرجاع JSON للـ AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.complaints-suggestions.partials.tbody-rows', [
                    'complaintsSuggestions' => $complaintsSuggestions
                ])->render(),
                'pagination' => view('admin.complaints-suggestions.partials.pagination', [
                    'complaintsSuggestions' => $complaintsSuggestions
                ])->render(),
                'count' => $complaintsSuggestions->total(),
                'stats' => $stats,
            ]);
        }

        // إرجاع الصفحة العادية
        return view('admin.complaints-suggestions.index', [
            'complaintsSuggestions' => $complaintsSuggestions,
            'stats' => $stats,
        ]);
    }

    /**
     * عرض تفاصيل شكوى/مقترح
     */
    public function show(ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // التحقق من الصلاحيات
        if (!$this->canAccess($user, $complaintSuggestion)) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الشكوى');
        }

        $complaintSuggestion->load(['operator', 'generator', 'responder']);

        return view('admin.complaints-suggestions.show', compact('complaintSuggestion'));
    }

    /**
     * عرض صفحة التعديل
     */
    public function edit(ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $complaintSuggestion->load(['operator', 'generator', 'responder']);

        return view('admin.complaints-suggestions.edit', compact('complaintSuggestion'));
    }

    /**
     * تحديث شكوى/مقترح
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
            'response' => $validated['response'] ?? null,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم تحديث الطلب بنجاح');
    }

    /**
     * الرد على شكوى/مقترح
     */
    public function respond(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        if (!$this->canAccess($user, $complaintSuggestion)) {
            abort(403);
        }

        $validated = $request->validate([
            'response' => 'required|string|max:5000',
            'status' => 'required|in:pending,in_progress,resolved,rejected',
        ]);

        $complaintSuggestion->update([
            'status' => $validated['status'],
            'response' => $validated['response'],
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم إرسال الرد بنجاح');
    }

    /**
     * حذف شكوى/مقترح
     */
    public function destroy(ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $complaintSuggestion->delete();

        return redirect()->route('admin.complaints-suggestions.index')
            ->with('success', 'تم حذف الطلب بنجاح');
    }

    /**
     * إغلاق الشكوى من قبل المشغل
     */
    public function closeByOperator(Request $request, ComplaintSuggestion $complaintSuggestion)
    {
        $user = Auth::user();
        
        // التحقق من أن الشكوى مرتبطة بمشغل المستخدم
        if ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id')->toArray();
            if (!$complaintSuggestion->operator_id || !in_array($complaintSuggestion->operator_id, $operatorIds)) {
                abort(403, 'هذه الشكوى غير مرتبطة بمشغلك');
            }
        } else {
            abort(403, 'غير مصرح لك بإغلاق هذه الشكوى');
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
            "تم إغلاق ".($complaintSuggestion->type === 'complaint' ? 'شكوى' : 'مقترح')." من قبل المشغل: {$complaintSuggestion->operator->name}",
            route('admin.complaints-suggestions.show', $complaintSuggestion)
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إغلاق الشكوى بنجاح',
            ]);
        }

        return redirect()->route('admin.complaints-suggestions.show', $complaintSuggestion)
            ->with('success', 'تم إغلاق الشكوى بنجاح');
    }

    /**
     * الحصول على جميع IDs المشغلين المرتبطين بالمستخدم
     */
    private function getUserOperatorIds($user): array
    {
        $operatorIds = [];

        // 1. المشغلين الذين يملكهم المستخدم (CompanyOwner)
        if ($user->isCompanyOwner()) {
            $ownedIds = $user->ownedOperators()->pluck('id')->toArray();
            $operatorIds = array_merge($operatorIds, $ownedIds);
        }

        // 2. المشغلين المرتبطين بالمستخدم من خلال علاقة many-to-many (Employee, Technician, etc.)
        $linkedIds = $user->operators()->pluck('operators.id')->toArray();
        $operatorIds = array_merge($operatorIds, $linkedIds);

        // 3. المشغل المرتبط بالدور المخصص (custom role) إذا كان موجوداً
        if ($user->roleModel && $user->roleModel->operator_id) {
            $operatorIds[] = $user->roleModel->operator_id;
        }

        // إزالة التكرارات وإرجاع array
        return array_values(array_unique(array_filter($operatorIds)));
    }

    /**
     * التحقق من إمكانية الوصول للشكوى
     */
    private function canAccess($user, ComplaintSuggestion $complaintSuggestion): bool
    {
        // السوبر ادمن والادمن وسلطة الطاقة يمكنهم الوصول لكل شيء
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        // يجب أن تكون الشكوى مرتبطة بمشغل
        if (!$complaintSuggestion->operator_id) {
            return false;
        }

        // التحقق من أن المستخدم مرتبط بهذا المشغل
        $operatorIds = $this->getUserOperatorIds($user);
        return in_array($complaintSuggestion->operator_id, $operatorIds);
    }

    /**
     * حساب الإحصائيات
     */
    private function calculateStats($query): array
    {
        return [
            'total' => (clone $query)->count(),
            'complaints' => (clone $query)->where('type', 'complaint')->count(),
            'suggestions' => (clone $query)->where('type', 'suggestion')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
        ];
    }

    /**
     * تنظيف مدخلات البحث
     */
    protected function sanitizeSearchInput(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        $input = trim($input);
        $input = strip_tags($input);
        $input = preg_replace('/[;\'"\\\]/', '', $input);
        $input = mb_substr($input, 0, 255);

        return $input;
    }
}
