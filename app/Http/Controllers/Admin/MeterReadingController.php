<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMeterReadingRequest;
use App\Http\Requests\Admin\UpdateMeterReadingRequest;
use App\Models\MeterReading;
use App\Models\Subscriber;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of meter readings.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', MeterReading::class);

        $user = auth()->user();
        $query = MeterReading::with(['subscriber', 'creator', 'updater']);

        // تحديد المشغل بناءً على دور المستخدم
        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $operatorIds = [$currentOperator->id];
                $query->whereHas('subscriber.generationUnits', function($q) use ($operatorIds) {
                    $q->whereIn('operator_id', $operatorIds);
                });
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $operatorIds = $operators->pluck('id')->toArray();
                $query->whereHas('subscriber.generationUnits', function($q) use ($operatorIds) {
                    $q->whereIn('operator_id', $operatorIds);
                });
                $currentOperator = $operators->first();
            }
        }

        // فلترة حسب المشغل
        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
        if ($canSelectOperator) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->whereHas('subscriber.generationUnits', function($q) use ($operatorId) {
                    $q->where('operator_id', $operatorId);
                });
            }
        }

        // فلترة حسب المشترك
        $subscriberId = (int) $request->input('subscriber_id', 0);
        if ($subscriberId > 0) {
            $query->where('subscriber_id', $subscriberId);
        }

        // فلترة حسب حالة القراءة
        $readingStatus = $request->input('reading_status', '');
        if ($readingStatus !== '' && in_array($readingStatus, ['1', '2'])) {
            $query->where('reading_status', $readingStatus);
        }

        // البحث
        $search = $request->input('search', '');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reading_number', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%")
                  ->orWhereHas('subscriber', function($q) use ($search) {
                      $q->where('subscription_number', 'like', "%{$search}%")
                        ->orWhere('subscriber_name', 'like', "%{$search}%");
                  });
            });
        }

        $meterReadings = $query->latest('reading_date')->latest()->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.meter-readings.partials.list', compact('meterReadings'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $meterReadings->total(),
            ]);
        }

        // جلب المشغلين للفلترة
        $operators = collect();
        if ($canSelectOperator) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        // جلب المشتركين للفلترة
        $subscribers = collect();
        if ($currentOperator) {
            $subscribers = Subscriber::whereHas('generationUnits', function($q) use ($currentOperator) {
                $q->where('operator_id', $currentOperator->id);
            })->select('id', 'subscription_number', 'subscriber_name')
              ->orderBy('subscription_number')
              ->get();
        } elseif ($canSelectOperator && $operatorId > 0) {
            $subscribers = Subscriber::whereHas('generationUnits', function($q) use ($operatorId) {
                $q->where('operator_id', $operatorId);
            })->select('id', 'subscription_number', 'subscriber_name')
              ->orderBy('subscription_number')
              ->get();
        }

        return view('admin.meter-readings.index', compact('meterReadings', 'operators', 'subscribers', 'currentOperator', 'canSelectOperator'));
    }

    /**
     * Show the form for creating a new meter reading.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', MeterReading::class);

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
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
            $operators = $user->ownedOperators;
        } elseif ($user->hasPermission('meter_readings.create') || $user->isTechnician() || ($user->isEmployee() && $user->hasPermission('meter_readings.create'))) {
            if ($user->operators()->exists()) {
                $operator = $user->operators()->first();
                $operators = $user->operators;
            } elseif ($user->ownedOperators()->exists()) {
                $operator = $user->ownedOperators()->first();
                $operators = $user->ownedOperators;
            } else {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        } else {
            return redirect()->route('admin.dashboard')
                ->with('error', 'ليس لديك صلاحية لإضافة قراءات.');
        }

        // جلب المشتركين النشطين فقط
        $subscribers = collect();
        if ($operator) {
            $subscribers = Subscriber::whereHas('generationUnits', function($q) use ($operator) {
                $q->where('operator_id', $operator->id);
            })->where('subscription_status', 1) // نشط فقط
              ->select('id', 'subscription_number', 'subscriber_name', 'meter_number')
              ->orderBy('subscription_number')
              ->get();
        } elseif ($user->isSuperAdmin()) {
            $subscribers = Subscriber::where('subscription_status', 1)
                ->select('id', 'subscription_number', 'subscriber_name', 'meter_number')
                ->orderBy('subscription_number')
                ->get();
        }

        // إذا تم تحديد مشترك في الطلب
        $selectedSubscriber = null;
        $lastReading = null;
        if ($request->has('subscriber_id')) {
            $selectedSubscriber = Subscriber::find($request->subscriber_id);
            if ($selectedSubscriber) {
                $lastReading = MeterReading::getLastReadingForSubscriber($selectedSubscriber->id);
            }
        }

        return view('admin.meter-readings.create', compact('operators', 'subscribers', 'operator', 'selectedSubscriber', 'lastReading'));
    }

    /**
     * Store a newly created meter reading in storage.
     */
    public function store(StoreMeterReadingRequest $request): RedirectResponse
    {
        $this->authorize('create', MeterReading::class);

        try {
            $data = $request->validated();
            
            // توليد رقم القراءة
            $data['reading_number'] = MeterReading::generateReadingNumber($data['subscriber_id']);
            
            $meterReading = MeterReading::create($data);

            return redirect()->route('admin.meter-readings.index')
                ->with('success', 'تم إضافة قراءة العداد بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified meter reading.
     */
    public function show(MeterReading $meterReading): View
    {
        $this->authorize('view', $meterReading);

        $meterReading->load(['subscriber.generationUnits.operator', 'creator', 'updater']);

        return view('admin.meter-readings.show', compact('meterReading'));
    }

    /**
     * Show the form for editing the specified meter reading.
     */
    public function edit(MeterReading $meterReading): View|RedirectResponse
    {
        $this->authorize('update', $meterReading);

        $user = auth()->user();
        $operators = collect();
        $operator = null;

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            $operators = $user->ownedOperators;
        } elseif ($user->hasPermission('meter_readings.update') || $user->isTechnician() || ($user->isEmployee() && $user->hasPermission('meter_readings.update'))) {
            if ($user->operators()->exists()) {
                $operator = $user->operators()->first();
                $operators = $user->operators;
            } elseif ($user->ownedOperators()->exists()) {
                $operator = $user->ownedOperators()->first();
                $operators = $user->ownedOperators;
            }
        }

        // جلب المشتركين النشطين فقط
        $subscribers = collect();
        if ($operator) {
            $subscribers = Subscriber::whereHas('generationUnits', function($q) use ($operator) {
                $q->where('operator_id', $operator->id);
            })->where('subscription_status', 1)
              ->select('id', 'subscription_number', 'subscriber_name', 'meter_number')
              ->orderBy('subscription_number')
              ->get();
        } elseif ($user->isSuperAdmin()) {
            $subscribers = Subscriber::where('subscription_status', 1)
                ->select('id', 'subscription_number', 'subscriber_name', 'meter_number')
                ->orderBy('subscription_number')
                ->get();
        }

        $meterReading->load('subscriber');
        
        // جلب آخر قراءة قبل هذه القراءة
        $lastReading = MeterReading::where('subscriber_id', $meterReading->subscriber_id)
            ->where('id', '!=', $meterReading->id)
            ->orderBy('reading_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return view('admin.meter-readings.edit', compact('meterReading', 'operators', 'subscribers', 'operator', 'lastReading'));
    }

    /**
     * Update the specified meter reading in storage.
     */
    public function update(UpdateMeterReadingRequest $request, MeterReading $meterReading): RedirectResponse
    {
        $this->authorize('update', $meterReading);

        try {
            $meterReading->update($request->validated());

            return redirect()->route('admin.meter-readings.index')
                ->with('success', 'تم تحديث قراءة العداد بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified meter reading from storage.
     */
    public function destroy(MeterReading $meterReading): RedirectResponse
    {
        $this->authorize('delete', $meterReading);

        try {
            $meterReading->delete();

            return redirect()->route('admin.meter-readings.index')
                ->with('success', 'تم حذف قراءة العداد بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Get last reading for subscriber (AJAX)
     */
    public function getLastReading(Request $request): JsonResponse
    {
        $subscriberId = $request->input('subscriber_id');
        
        if (!$subscriberId) {
            return response()->json(['success' => false, 'message' => 'رقم الاشتراك مطلوب']);
        }

        $subscriber = Subscriber::find($subscriberId);
        if (!$subscriber) {
            return response()->json(['success' => false, 'message' => 'المشترك غير موجود']);
        }

        if ($subscriber->subscription_status != 1) {
            return response()->json([
                'success' => false, 
                'message' => 'لا يمكن إدخال قراءة لاشتراك موقوف أو مغلق'
            ]);
        }

        $lastReading = MeterReading::getLastReadingForSubscriber($subscriberId);
        
        return response()->json([
            'success' => true,
            'last_reading' => $lastReading ? [
                'previous_reading' => $lastReading->current_reading,
                'reading_date' => $lastReading->reading_date->format('Y-m-d'),
                'meter_number' => $lastReading->meter_number,
            ] : [
                'previous_reading' => 0,
                'reading_date' => null,
                'meter_number' => $subscriber->meter_number ?? '',
            ],
            'subscriber' => [
                'meter_number' => $subscriber->meter_number ?? '',
            ]
        ]);
    }
}
