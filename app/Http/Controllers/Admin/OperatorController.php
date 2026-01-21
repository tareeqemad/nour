<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorRequest;
use App\Models\Notification;
use App\Models\Operator;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OperatorController extends Controller
{
    /**
     * عرض المشغلين الذين يحتاجون إلى اعتماد (للسلطة الطاقة فقط)
     */
    public function pendingApproval(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Operator::class);
        
        $authUser = $request->user();
        
        // فقط سلطة الطاقة يمكنها الوصول لهذه الصفحة
        if (!$authUser->isEnergyAuthority() && !$authUser->isSuperAdmin() && !$authUser->isAdmin()) {
            abort(403);
        }

        $name = trim((string) $request->get('name', ''));
        $status = trim((string) $request->get('status', ''));

        $operatorsQuery = Operator::query()
            ->with('owner')
            ->where('is_approved', false) // فقط غير المعتمدين
            ->withCount([
                'generationUnits',
                'users as employees_count' => function ($q) {
                    $q->whereIn('role', [Role::Employee, Role::Technician]);
                },
            ]);

        // Search by name
        if ($name !== '') {
            $operatorsQuery->where(function ($sub) use ($name) {
                $sub->where('name', 'like', "%{$name}%")
                    ->orWhere('owner_name', 'like', "%{$name}%")
                    ->orWhereHas('owner', function ($oq) use ($name) {
                        $oq->where('name', 'like', "%{$name}%")
                           ->orWhere('username', 'like', "%{$name}%")
                           ->orWhere('email', 'like', "%{$name}%");
                    });
            });
        }

        // Status filter (active/inactive)
        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $operatorsQuery->where('status', $status);
        }

        $operators = $operatorsQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // AJAX: رجّع partial list فقط
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.operators.partials.pending-list', compact('operators'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return view('admin.operators.pending-approval', [
            'operators' => $operators,
            'status' => $status,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Operator::class);

        $authUser = $request->user();

        $name = trim((string) $request->get('name', ''));
        $status = trim((string) $request->get('status', ''));

        $operatorsQuery = Operator::query()
            ->with('owner')
            ->withCount([
                'generationUnits',
                'users as employees_count' => function ($q) {
                    // غالبًا جدول pivot operator_user فيه فقط موظفين/فنيين
                    // ومع ذلك نخليه فلترة احتياطية حسب enum القديم
                    $q->whereIn('role', [Role::Employee, Role::Technician]);
                },
            ]);

        // Scope حسب الدور
        if ($authUser->isCompanyOwner()) {
            $operatorsQuery->where('owner_id', $authUser->id);
        } elseif ($authUser->isEmployee() || $authUser->isTechnician()) {
            $operatorIds = $authUser->operators()->pluck('operators.id');
            $operatorsQuery->whereIn('id', $operatorIds);
        }

        // Search by name
        if ($name !== '') {
            $operatorsQuery->where(function ($sub) use ($name) {
                $sub->where('name', 'like', "%{$name}%")
                    ->orWhere('unit_name', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%")
                    ->orWhereHas('owner', function ($oq) use ($name) {
                        $oq->where('name', 'like', "%{$name}%")
                           ->orWhere('username', 'like', "%{$name}%")
                           ->orWhere('email', 'like', "%{$name}%")
                           ->orWhere('phone', 'like', "%{$name}%");
                    });
            });
        }

        // Status filter (active/inactive)
        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $operatorsQuery->where('status', $status);
        }

        $operators = $operatorsQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // AJAX: رجّع partial list فقط
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.operators.partials.list', compact('operators'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        // للي مش SuperAdmin و EnergyAuthority: غالبًا رح يكون عنده مشغل واحد
        $myOperator = (! $authUser->isSuperAdmin() && ! $authUser->isEnergyAuthority() && ! $authUser->isAdmin()) 
            ? $operators->first() 
            : null;

        return view('admin.operators.index', [
            'operators' => $operators,
            'status' => $status,
            'myOperator' => $myOperator,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @deprecated المشغلون الآن يقدمون طلبات انضمام من الموقع العام
     */
    public function create(Request $request): View
    {
        abort(404, 'تم إلغاء هذه الصفحة. المشغلون يقدمون طلبات انضمام من الموقع العام.');
    }


    /**
     * Toggle operator status (active/inactive) - فقط التفعيل/الإيقاف
     */
    public function toggleStatus(Request $request, Operator $operator): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $operator);

        // فقط السوبر أدمن و Admin و Energy Authority يمكنهم تغيير الحالة
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isEnergyAuthority()) {
            abort(403, 'لا تملك صلاحية لتغيير حالة المشغل');
        }

        $oldStatus = $operator->status;
        
        // تبديل الحالة فقط (active/inactive)
        $operator->status = $operator->status === 'active' ? 'inactive' : 'active';
        $operator->save();

        if ($operator->status === 'active') {
            // عند التفعيل: تفعيل المشغل (owner) والموظفين التابعين له
            if ($operator->owner) {
                $operator->owner->update(['status' => 'active']);
            }
            $operator->users()->update(['status' => 'active']);
            
            // إرسال إشعار للمشغل عند التفعيل
            if ($operator->owner) {
                \App\Models\Notification::createNotification(
                    $operator->owner_id,
                    'operator_activated',
                    'تم تفعيل حسابك',
                    "تم تفعيل حسابك في منصة " . \App\Models\Setting::get('site_name', 'نور') . ". يمكنك الآن الوصول للنظام.",
                    route('admin.operators.profile')
                );
            }
            
            $message = "تم تفعيل المشغل بنجاح";
        } else {
            // عند الإيقاف: إيقاف المشغل (owner) وجميع الموظفين التابعين له
            if ($operator->owner) {
                $operator->owner->update(['status' => 'inactive']);
            }
            $operator->users()->update(['status' => 'inactive']);
            
            // Get site name from settings
            $siteName = \App\Models\Setting::get('site_name', 'نور');
            
            // الحصول على system user (منصة نور) لإرسال الرسائل منه
            $systemUser = \App\Models\User::where('username', 'platform_rased')->first();
            
            if ($systemUser) {
                // إرسال رسالة للمشغل (owner)
                if ($operator->owner) {
                    \App\Models\Message::create([
                        'sender_id' => $systemUser->id,
                        'receiver_id' => $operator->owner_id,
                        'operator_id' => $operator->id,
                        'subject' => 'تم إيقاف حسابك',
                        'body' => "عزيزي/عزيزتي {$operator->owner->name}،\n\nنود إعلامك بأنه تم إيقاف حسابك في منصة {$siteName}.\n\nلن تتمكن من الوصول للنظام حتى يتم تفعيل حسابك مرة أخرى من قبل سلطة الطاقة.\n\nإذا كان لديك أي استفسار، يرجى التواصل معنا.",
                        'type' => 'admin_to_operator',
                        'is_read' => false,
                    ]);
                    
                    // إرسال إشعار أيضاً
                    \App\Models\Notification::createNotification(
                        $operator->owner_id,
                        'operator_deactivated',
                        'تم إيقاف حسابك',
                        "تم إيقاف حسابك في منصة {$siteName}. لن تتمكن من الوصول للنظام حتى يتم تفعيل حسابك مرة أخرى.",
                        route('admin.operators.profile')
                    );
                }
                
                // إرسال رسائل لجميع الموظفين التابعين للمشغل
                $operator->users()
                    ->whereIn('role', [\App\Enums\Role::Employee, \App\Enums\Role::Technician])
                    ->each(function ($employee) use ($systemUser, $operator) {
                        \App\Models\Message::create([
                            'sender_id' => $systemUser->id,
                            'receiver_id' => $employee->id,
                            'operator_id' => $operator->id,
                            'subject' => 'تم إيقاف حسابك',
                            'body' => "عزيزي/عزيزتي {$employee->name}،\n\nنود إعلامك بأنه تم إيقاف حساب المشغل الذي تعمل لديه ({$operator->name})، وبالتالي تم إيقاف حسابك أيضاً.\n\nلن تتمكن من الوصول للنظام حتى يتم تفعيل حساب المشغل مرة أخرى من قبل سلطة الطاقة.\n\nإذا كان لديك أي استفسار، يرجى التواصل معنا.",
                            'type' => 'admin_to_operator',
                            'is_read' => false,
                        ]);
                        
                        // إرسال إشعار أيضاً
                        \App\Models\Notification::createNotification(
                            $employee->id,
                            'operator_deactivated',
                            'تم إيقاف حسابك',
                            "تم إيقاف حساب المشغل الذي تعمل لديه، وبالتالي تم إيقاف حسابك أيضاً.",
                            route('admin.dashboard')
                        );
                    });
            }
            
            $message = "تم إيقاف المشغل بنجاح (تم إيقاف المشغل وجميع الموظفين التابعين له وتم إرسال رسائل لهم)";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'operator' => [
                    'id' => $operator->id,
                    'status' => $operator->status,
                    'is_approved' => $operator->is_approved,
                ],
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle operator approval (is_approved) - فقط الاعتماد/إلغاء الاعتماد
     */
    /**
     * Toggle operator approval (is_approved) - only approval/activation
     * Only Super Admin, Admin, and Energy Authority with operators.approve permission can approve operators
     */
    public function toggleApproval(Request $request, Operator $operator): RedirectResponse|JsonResponse
    {
        // Check permission: Only users with operators.approve permission can approve operators
        $this->authorize('approve', $operator);

        $oldApproved = $operator->is_approved;
        
        // تبديل الاعتماد فقط
        $operator->is_approved = !$operator->is_approved;
        
        // عند الاعتماد: التأكد من أن status = 'active' أيضاً
        if ($operator->is_approved && $operator->status !== 'active') {
            $operator->status = 'active';
            // تفعيل المشغل (owner) والموظفين التابعين له
            if ($operator->owner) {
                $operator->owner->update(['status' => 'active']);
            }
            $operator->users()->update(['status' => 'active']);
        }
        
        $operator->save();

        if ($operator->is_approved) {
            // عند الاعتماد: إرسال رسالة وإشعار للمشغل
            // Get site name from settings
            $siteName = \App\Models\Setting::get('site_name', 'نور');
            
            // الرسالة دائماً تظهر "من سلطة الطاقة" بغض النظر عن من قام بالاعتماد
            if ($operator->owner) {
                // الحصول على system user (منصة نور) لإرسال الرسالة منه
                $systemUser = \App\Models\User::where('username', 'platform_rased')->first();
                
                if ($systemUser) {
                    // إرسال رسالة للمشغل - الرسالة دائماً تظهر "من سلطة الطاقة"
                    \App\Models\Message::create([
                        'sender_id' => $systemUser->id,
                        'receiver_id' => $operator->owner_id,
                        'operator_id' => $operator->id,
                        'subject' => 'تم اعتماد حسابك من قبل سلطة الطاقة',
                        'body' => "عزيزي/عزيزتي {$operator->owner->name}،\n\nنود إعلامك بأنه تم اعتماد حسابك في منصة {$siteName} من قبل سلطة الطاقة.\n\nيمكنك الآن الوصول لجميع خصائص النظام:\n- إضافة موظفين وفنيين\n- إدارة الصلاحيات\n- إدارة الشجرة\n- الوصول لجميع السجلات\n\nنتمنى لك تجربة ممتعة مع منصة {$siteName}.",
                        'type' => 'admin_to_operator',
                        'is_read' => false,
                    ]);
                }
                
                // إرسال إشعار أيضاً - الرسالة دائماً تظهر "من سلطة الطاقة"
                \App\Models\Notification::createNotification(
                    $operator->owner_id,
                    'operator_approved',
                    'تم اعتماد حسابك من قبل سلطة الطاقة',
                    "تم اعتماد حسابك في منصة {$siteName} من قبل سلطة الطاقة. يمكنك الآن الوصول لجميع خصائص النظام.",
                    route('admin.operators.profile')
                );
            }
            $message = "تم اعتماد المشغل بنجاح";
        } else {
            $message = "تم إلغاء اعتماد المشغل";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'operator' => [
                    'id' => $operator->id,
                    'status' => $operator->status,
                    'is_approved' => $operator->is_approved,
                ],
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Display detailed information about the specified operator.
     */
    public function show(Operator $operator): View
    {
        $this->authorize('view', $operator);

        $operator->load([
            'owner',
            'generationUnits.statusDetail',
            'generationUnits.generators' => function ($q) {
                $q->latest()->take(5);
            },
            'users',
            'operationLogs' => function ($q) {
                $q->latest()->take(5);
            },
        ]);

        $operator->loadCount([
            'generationUnits',
            'generators',
            'users',
            'operationLogs',
        ]);

        return view('admin.operators.show', compact('operator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Operator $operator): View
    {
        $this->authorize('update', $operator);

        $operator->load('owner');

        // للـ AJAX مودال: رجع form partial فقط
        if ($request->ajax()) {
            return view('admin.operators.partials.form', [
                'mode' => 'edit',
                'operator' => $operator,
            ]);
        }

        return view('admin.operators.edit', compact('operator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperatorRequest $request, Operator $operator): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $operator);

        $operator->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
        ]);

        // تحديث بيانات المستخدم المالك
        if ($operator->owner) {
            $auth = auth()->user();
            $userData = [];

            // SuperAdmin فقط يغير username
            if ($auth->isSuperAdmin() && $request->filled('username')) {
                $userData['username'] = $request->validated('username');
            }

            if ($request->filled('user_email')) {
                $userData['email'] = $request->validated('user_email');
            }

            if ($request->filled('password')) {
                $plainPassword = $request->validated('password');
                $userData['password'] = Hash::make($plainPassword);
            }

            if (!empty($userData)) {
                $operator->owner->update($userData);
            }
        }

        $msg = 'تم تحديث بيانات المشغل بنجاح.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.operators.index')->with('success', $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Operator $operator): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $operator);

        try {
            if ($operator->owner) {
                $operator->owner->delete();
            }
            $operator->delete();
        } catch (\Throwable $e) {
            $msg = 'تعذر حذف المشغل بسبب بيانات مرتبطة.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.operators.index')->with('error', $msg);
        }

        $msg = 'تم حذف المشغل بنجاح.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.operators.index')->with('success', $msg);
    }

    /**
     * توليد رقم المولد التالي للمشغل
     */
    public function generateGeneratorNumber(Request $request, Operator $operator): JsonResponse
    {
        $this->authorize('view', $operator);

        $generatorNumber = \App\Models\Generator::getNextGeneratorNumber($operator->id);

        if (!$generatorNumber) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر توليد رقم المولد. تأكد من أن المشغل لديه unit_code وأن عدد المولدات لم يتجاوز 99.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'generator_number' => $generatorNumber,
        ]);
    }

    /**
     * الحصول على المشغلين حسب المحافظة
     */
    public function getByGovernorate(Request $request, int $governorate): JsonResponse
    {
        $activeOnly = $request->boolean('active_only', true);
        
        $operators = GeneralHelper::getOperatorsByGovernorateSimple($governorate, $activeOnly);

        return response()->json([
            'success' => true,
            'data' => $operators->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'city' => $operator->getCityName(),
                    'status' => $operator->status,
                ];
            }),
        ]);
    }
}
