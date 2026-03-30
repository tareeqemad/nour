<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriberRequest;
use App\Http\Requests\Admin\UpdateSubscriberRequest;
use App\Exports\SubscribersExport;
use App\Models\MeterReading;
use App\Models\Subscriber;
use App\Models\GenerationUnit;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * بناء query الفلترة المشترك بين index و export
     */
    private function buildFilteredQuery(Request $request): array
    {
        $user = auth()->user();
        $query = Subscriber::with(['creator', 'updater', 'generationUnits.operator']);

        // تحديد المشغل بناءً على دور المستخدم
        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $query->whereHas('generationUnits', fn($q) => $q->where('operator_id', $currentOperator->id));
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $operatorIds = $operators->pluck('id')->toArray();
                $query->whereHas('generationUnits', fn($q) => $q->whereIn('operator_id', $operatorIds));
                $currentOperator = $operators->first();
            }
        }

        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();

        // فلتر المشغل
        if ($canSelectOperator) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->whereHas('generationUnits', fn($q) => $q->where('operator_id', $operatorId));
            }
        }

        // فلتر وحدة التوليد
        $unitId = (int) $request->input('generation_unit_id', 0);
        if ($unitId > 0) {
            $query->whereHas('generationUnits', fn($q) => $q->where('generation_units.id', $unitId));
        }

        // فلتر حالة الاشتراك
        $status = $request->input('subscription_status', '');
        if ($status !== '' && in_array($status, ['1', '2', '3'])) {
            $query->where('subscription_status', $status);
        }

        // فلتر تصنيف الاشتراك
        $category = $request->input('subscription_category', '');
        if ($category !== '' && in_array($category, ['1', '2', '3', '4'])) {
            $query->where('subscription_category', $category);
        }

        // فلتر نوع الفاز
        $phase = $request->input('phase_type', '');
        if ($phase !== '' && in_array($phase, ['1', '2'])) {
            $query->where('phase_type', $phase);
        }

        // فلتر نوع الخدمة
        $service = $request->input('service_type', '');
        if ($service !== '' && in_array($service, ['1', '2', '3'])) {
            $query->where('service_type', $service);
        }

        // فلتر الأمبير
        $ampere = $request->input('ampere', '');
        if ($ampere !== '') {
            $query->where('ampere', $ampere);
        }

        // فلتر نوع المشترك (موظف / عادي)
        $isEmployee = $request->input('is_employee', '');
        if ($isEmployee !== '' && in_array($isEmployee, ['0', '1'])) {
            $query->where('is_employee_subscription', (bool) $isEmployee);
        }

        // فلتر رقم الاشتراك
        $subscriptionNumber = $request->input('subscription_number', '');
        if ($subscriptionNumber !== '') {
            $query->where('subscription_number', 'like', "%{$subscriptionNumber}%");
        }

        // فلتر رقم الصندوق
        $boxNumber = $request->input('box_number', '');
        if ($boxNumber !== '') {
            $query->where('box_number', $boxNumber);
        }

        // البحث النصي
        $search = $request->input('search', '');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subscription_number', 'like', "%{$search}%")
                  ->orWhere('subscriber_id_number', 'like', "%{$search}%")
                  ->orWhere('subscriber_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        return [$query, $currentOperator, $canSelectOperator];
    }

    /**
     * Display a listing of subscribers.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Subscriber::class);

        [$query, $currentOperator, $canSelectOperator] = $this->buildFilteredQuery($request);
        $subscribers = $query->latest()->paginate(100);

        if ($request->ajax() || $request->wantsJson()) {
            $page = (int) $request->input('page', 1);
            $append = $page > 1; // append mode for infinite scroll
            $html = view('admin.subscribers.partials.list', compact('subscribers', 'append'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $subscribers->total(),
                'has_more' => $subscribers->hasMorePages(),
                'next_page' => $subscribers->hasMorePages() ? $page + 1 : null,
                'append' => $append,
            ]);
        }

        $user = auth()->user();

        // جلب المشغلين للفلترة
        $operators = $canSelectOperator
            ? Operator::select('id', 'name')->orderBy('name')->get()
            : collect();

        // جلب وحدات التوليد
        if ($user->isSuperAdmin()) {
            $generationUnits = GenerationUnit::with('operator:id,name')
                ->select('id', 'name', 'unit_code', 'operator_id')->orderBy('name')->get();
        } elseif ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id');
            $generationUnits = GenerationUnit::whereIn('operator_id', $operatorIds)
                ->with('operator:id,name')->select('id', 'name', 'unit_code', 'operator_id')->orderBy('name')->get();
        } else {
            $operatorIds = $user->operators()->pluck('operators.id');
            $generationUnits = GenerationUnit::whereIn('operator_id', $operatorIds)
                ->with('operator:id,name')->select('id', 'name', 'unit_code', 'operator_id')->orderBy('name')->get();
        }

        return view('admin.subscribers.index', compact('subscribers', 'operators', 'currentOperator', 'canSelectOperator', 'generationUnits'));
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
            $data = $request->validated();

            // تعيين تاريخ الاشتراك الافتراضي إذا لم يُحدد
            if (empty($data['subscription_date'])) {
                $data['subscription_date'] = now()->toDateString();
            }

            // توليد رقم الاشتراك تلقائياً
            if (!empty($request->generation_unit_ids) && is_array($request->generation_unit_ids)) {
                $firstUnitId = $request->generation_unit_ids[0];
                $phaseType = $data['phase_type'];
                
                $data['subscription_number'] = Subscriber::generateSubscriptionNumber($firstUnitId, $phaseType);
            } else {
                throw new \Exception('يجب اختيار وحدة توليد واحدة على الأقل لتوليد رقم الاشتراك');
            }
            
            $subscriber = Subscriber::create($data);

            // ربط وحدات التوليد
            if ($request->has('generation_unit_ids') && is_array($request->generation_unit_ids)) {
                $subscriber->generationUnits()->sync($request->generation_unit_ids);
            }

            return redirect()->route('admin.subscribers.index')
                ->with('success', "تم إضافة المشترك بنجاح. رقم الاشتراك: {$subscriber->subscription_number}");
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '23000') && str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'البيانات المدخلة موجودة مسبقاً، يرجى التحقق من البيانات وإعادة المحاولة.');
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المشترك، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Display the specified subscriber.
     */
    public function show(Subscriber $subscriber): View
    {
        $this->authorize('view', $subscriber);

        $subscriber->load(['creator', 'updater', 'generationUnits.operator']);

        // إحصائيات الفواتير
        $invoiceStats = [
            'total' => $subscriber->invoices()->whereNotIn('invoice_status', [0, 4])->count(),
            'total_amount' => (float) $subscriber->invoices()->whereNotIn('invoice_status', [0, 4])->sum('invoice_amount'),
            'paid' => (float) \App\Models\Payment::whereIn('invoice_id', $subscriber->invoices()->pluck('id'))->sum('amount_paid'),
            'unpaid_count' => $subscriber->invoices()->where('invoice_status', 1)->count(),
            'draft_count' => $subscriber->invoices()->where('invoice_status', 0)->count(),
        ];
        $invoiceStats['balance'] = $invoiceStats['total_amount'] - $invoiceStats['paid'];

        // آخر قراءة
        $lastReading = $subscriber->meterReadings()->latest('reading_date')->first();

        // آخر 5 فواتير
        $recentInvoices = $subscriber->invoices()->with('payments')->latest()->take(5)->get();

        return view('admin.subscribers.show', compact('subscriber', 'invoiceStats', 'lastReading', 'recentInvoices'));
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

            // تسجيل من قام بالتحديث
            $data['last_updated_by'] = $user->id;

            $subscriber->update($data);

            // تحديث ربط وحدات التوليد
            if ($request->has('generation_unit_ids')) {
                $subscriber->generationUnits()->sync($request->generation_unit_ids ?? []);
            }

            return redirect()->route('admin.subscribers.index')
                ->with('success', 'تم تحديث بيانات المشترك بنجاح.');
        } catch (\Exception $e) {
            // معالجة خاصة لأخطاء تكرار البيانات
            if (str_contains($e->getMessage(), 'unique_subscriber_phone')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['phone' => 'رقم الموبايل مسجل مسبقاً، يرجى استخدام رقم موبايل مختلف.']);
            }
            
            if (str_contains($e->getMessage(), 'unique_subscriber_meter_number')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['meter_number' => 'رقم العداد مسجل مسبقاً، يرجى استخدام رقم عداد مختلف.']);
            }
            
            if (str_contains($e->getMessage(), '23000') && str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'البيانات المدخلة موجودة مسبقاً، يرجى التحقق من البيانات وإعادة المحاولة.');
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث بيانات المشترك، يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Remove the specified subscriber from storage.
     */
    public function destroy(Subscriber $subscriber): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $subscriber);

        try {
            // حذف العلاقات أولاً
            $subscriber->generationUnits()->detach();
            
            // حذف المشترك نهائياً من قاعدة البيانات
            $subscriber->forceDelete();

            // إرجاع JSON للـ AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المشترك بنجاح.'
                ]);
            }

            return redirect()->route('admin.subscribers.index')
                ->with('success', 'تم حذف المشترك بنجاح.');
        } catch (\Exception $e) {
            \Log::error('Error deleting subscriber: ' . $e->getMessage());
            
            // إرجاع JSON للـ AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف المشترك: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف المشترك: ' . $e->getMessage());
        }
    }

    /**
     * Export subscribers to Excel.
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Subscriber::class);

        [$query] = $this->buildFilteredQuery($request);
        $subscribers = $query->with(['generationUnits.operator'])->get();

        return (new SubscribersExport($subscribers))->download();
    }

    /**
     * Get generation units for a specific operator (AJAX).
     */
    public function getGenerationUnits(Request $request, Operator $operator): JsonResponse
    {
        $generationUnits = $operator->generationUnits()
            ->select('id', 'name', 'unit_code', 'operator_id')
            ->orderBy('name')
            ->get()
            ->map(fn($unit) => [
                'id'        => $unit->id,
                'name'      => $unit->name,
                'unit_code' => $unit->unit_code,
                'label'     => "{$unit->name} ({$unit->unit_code})",
            ]);

        return response()->json([
            'success'          => true,
            'generation_units' => $generationUnits,
        ]);
    }

    /**
     * Check if a field value is unique (AJAX endpoint)
     */
    public function checkUnique(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $excludeId = $request->input('exclude_id'); // للتعديل
        
        $allowedFields = ['subscriber_id_number', 'phone', 'meter_number'];
        
        if (!in_array($field, $allowedFields) || empty($value)) {
            return response()->json(['exists' => false]);
        }
        
        $query = Subscriber::where($field, $value);
        
        // استبعاد السجل الحالي في حالة التعديل
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        $messages = [
            'subscriber_id_number' => 'رقم الهوية موجود مسبقاً',
            'phone' => 'رقم الموبايل موجود مسبقاً', 
            'meter_number' => 'رقم العداد موجود مسبقاً'
        ];
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? $messages[$field] : null
        ]);
    }
}
