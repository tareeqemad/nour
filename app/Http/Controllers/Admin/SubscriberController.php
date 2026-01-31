<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriberRequest;
use App\Http\Requests\Admin\UpdateSubscriberRequest;
use App\Models\Subscriber;
use App\Models\GenerationUnit;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * Display a listing of subscribers.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Subscriber::class);

        $user = auth()->user();
        $query = Subscriber::with(['creator', 'updater', 'generationUnits.operator']);

        // تحديد المشغل بناءً على دور المستخدم
        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $operatorIds = [$currentOperator->id];
                $query->whereHas('generationUnits', function($q) use ($operatorIds) {
                    $q->whereIn('operator_id', $operatorIds);
                });
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $operatorIds = $operators->pluck('id')->toArray();
                $query->whereHas('generationUnits', function($q) use ($operatorIds) {
                    $q->whereIn('operator_id', $operatorIds);
                });
                $currentOperator = $operators->first();
            }
        }

        // فلترة حسب المشغل (للأدوار التي يمكنها اختيار المشغل)
        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
        if ($canSelectOperator) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->whereHas('generationUnits', function($q) use ($operatorId) {
                    $q->where('operator_id', $operatorId);
                });
            }
        }

        // فلترة حسب حالة الاشتراك
        $subscriptionStatus = $request->input('subscription_status', '');
        if ($subscriptionStatus !== '' && in_array($subscriptionStatus, ['1', '2', '3'])) {
            $query->where('subscription_status', $subscriptionStatus);
        }

        // البحث
        $search = $request->input('search', '');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subscription_number', 'like', "%{$search}%")
                  ->orWhere('subscriber_id_number', 'like', "%{$search}%")
                  ->orWhere('subscriber_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%");
            });
        }

        $subscribers = $query->latest()->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.subscribers.partials.list', compact('subscribers'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $subscribers->total(),
            ]);
        }

        // جلب المشغلين للفلترة
        $operators = collect();
        if ($canSelectOperator) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        return view('admin.subscribers.index', compact('subscribers', 'operators', 'currentOperator', 'canSelectOperator'));
    }

    /**
     * Show the form for creating a new subscriber.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Subscriber::class);

        $user = auth()->user();
        $operators = collect();
        $operator = null;

        // تحديد المشغل بناءً على دور المستخدم
        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }
            $operators = $user->ownedOperators;
        } elseif ($user->hasPermission('subscribers.create') || $user->isTechnician() || ($user->isEmployee() && $user->hasPermission('subscribers.create'))) {
            if ($user->operators()->exists()) {
                $operator = $user->operators()->first();
                $operators = $user->operators;
            } elseif ($user->ownedOperators()->exists()) {
                $operator = $user->ownedOperators()->first();
                $operators = $user->ownedOperators;
            } else {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }
        } else {
            return redirect()->route('admin.dashboard')
                ->with('error', 'ليس لديك صلاحية لإضافة مشتركين.');
        }

        // جلب وحدات التوليد
        $generationUnits = collect();
        if ($operator) {
            $generationUnits = $operator->generationUnits()
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('name')
                ->get();
        } elseif ($user->isSuperAdmin()) {
            $generationUnits = GenerationUnit::select('id', 'name', 'unit_code', 'operator_id')
                ->with('operator:id,name')
                ->orderBy('name')
                ->get();
        }

        return view('admin.subscribers.create', compact('operators', 'generationUnits', 'operator'));
    }

    /**
     * Store a newly created subscriber in storage.
     */
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $this->authorize('create', Subscriber::class);

        try {
            $subscriber = Subscriber::create($request->validated());

            // ربط وحدات التوليد
            if ($request->has('generation_unit_ids') && is_array($request->generation_unit_ids)) {
                $subscriber->generationUnits()->sync($request->generation_unit_ids);
            }

            return redirect()->route('admin.subscribers.index')
                ->with('success', 'تم إضافة المشترك بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المشترك: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified subscriber.
     */
    public function show(Subscriber $subscriber): View
    {
        $this->authorize('view', $subscriber);

        $subscriber->load(['creator', 'updater', 'generationUnits.operator']);

        return view('admin.subscribers.show', compact('subscriber'));
    }

    /**
     * Show the form for editing the specified subscriber.
     */
    public function edit(Subscriber $subscriber): View|RedirectResponse
    {
        $this->authorize('update', $subscriber);

        $user = auth()->user();
        $operators = collect();
        $operator = null;

        // تحديد المشغل بناءً على دور المستخدم
        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            $operators = $user->ownedOperators;
        } elseif ($user->hasPermission('subscribers.update') || $user->isTechnician() || ($user->isEmployee() && $user->hasPermission('subscribers.update'))) {
            if ($user->operators()->exists()) {
                $operator = $user->operators()->first();
                $operators = $user->operators;
            } elseif ($user->ownedOperators()->exists()) {
                $operator = $user->ownedOperators()->first();
                $operators = $user->ownedOperators;
            }
        }

        // جلب وحدات التوليد
        $generationUnits = collect();
        if ($operator) {
            $generationUnits = $operator->generationUnits()
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('name')
                ->get();
        } elseif ($user->isSuperAdmin()) {
            $generationUnits = GenerationUnit::select('id', 'name', 'unit_code', 'operator_id')
                ->with('operator:id,name')
                ->orderBy('name')
                ->get();
        }

        $subscriber->load('generationUnits');

        return view('admin.subscribers.edit', compact('subscriber', 'operators', 'generationUnits', 'operator'));
    }

    /**
     * Update the specified subscriber in storage.
     */
    public function update(UpdateSubscriberRequest $request, Subscriber $subscriber): RedirectResponse
    {
        $this->authorize('update', $subscriber);

        try {
            $user = auth()->user();
            $data = $request->validated();
            
            // المشغل لا يمكنه تعديل رقم الاشتراك
            if (!$user->isSuperAdmin()) {
                unset($data['subscription_number']);
            }

            $subscriber->update($data);

            // تحديث ربط وحدات التوليد
            if ($request->has('generation_unit_ids')) {
                $subscriber->generationUnits()->sync($request->generation_unit_ids ?? []);
            }

            return redirect()->route('admin.subscribers.index')
                ->with('success', 'تم تحديث بيانات المشترك بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث بيانات المشترك: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subscriber from storage.
     */
    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $this->authorize('delete', $subscriber);

        try {
            $subscriber->delete();

            return redirect()->route('admin.subscribers.index')
                ->with('success', 'تم حذف المشترك بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف المشترك: ' . $e->getMessage());
        }
    }
}
