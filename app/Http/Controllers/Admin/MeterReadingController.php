<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMeterReadingRequest;
use App\Http\Requests\Admin\UpdateMeterReadingRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Subscriber;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // فلترة حسب حالة الإجراء
        $actionStatus = $request->input('action_status', '');
        if ($actionStatus !== '' && in_array($actionStatus, ['0', '1', '2'])) {
            $query->where('action_status', $actionStatus);
        }

        // فلترة حسب التاريخ
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');
        if ($dateFrom) {
            $query->whereDate('reading_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('reading_date', '<=', $dateTo);
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
            $data['meter_number'] = $data['meter_number'] ?? '';

            $meterReading = DB::transaction(function () use ($data) {
                // توليد رقم القراءة داخل Transaction مع قفل lockForUpdate
                $data['reading_number'] = MeterReading::generateReadingNumber($data['subscriber_id']);
                return MeterReading::create($data);
            });

            // سجل التدقيق
            AuditLog::log(
                'create',
                $meterReading,
                auth()->user(),
                null,
                $meterReading->toArray(),
                'إضافة قراءة عداد جديدة: ' . $meterReading->reading_number
            );

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

        // لا يُسمح بتعديل قراءة معتمدة أو مفوترة
        if (!$meterReading->isEditable()) {
            return redirect()->back()
                ->with('error', 'لا يمكن تعديل قراءة معتمدة أو مفوترة.');
        }

        try {
            $oldValues = $meterReading->toArray();
            $data = $request->validated();
            $data['meter_number'] = $data['meter_number'] ?? '';
            $meterReading->update($data);

            // سجل التدقيق
            AuditLog::log(
                'update',
                $meterReading,
                auth()->user(),
                $oldValues,
                $meterReading->fresh()->toArray(),
                'تعديل قراءة العداد: ' . $meterReading->reading_number
            );

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

        // لا يُسمح بحذف قراءة معتمدة أو مفوترة
        if (!$meterReading->isEditable()) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف قراءة معتمدة أو مفوترة.');
        }

        try {
            // سجل التدقيق قبل الحذف
            AuditLog::log(
                'delete',
                $meterReading,
                auth()->user(),
                $meterReading->toArray(),
                null,
                'حذف قراءة العداد: ' . $meterReading->reading_number
            );

            $meterReading->delete();

            return redirect()->route('admin.meter-readings.index')
                ->with('success', 'تم حذف قراءة العداد بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف قراءة العداد: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve selected meter readings.
     */
    public function bulkApprove(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', MeterReading::class);

        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'لم يتم تحديد أي قراءات.']);
            }
            return redirect()->back()->with('error', 'لم يتم تحديد أي قراءات.');
        }

        $user = auth()->user();
        $approvedCount = 0;
        $skippedCount  = 0;
        $billedCount   = 0;
        $abnormalCount = 0;

        $readings = MeterReading::with('subscriber')->whereIn('id', $ids)->get();

        foreach ($readings as $reading) {
            if (!$reading->isApprovable()) {
                $skippedCount++;
                continue;
            }

            // القراءات غير الطبيعية يجب اعتمادها بشكل منفرد مع ذكر السبب
            if ($reading->isAbnormal()) {
                $abnormalCount++;
                continue;
            }

            DB::transaction(function () use ($reading, $user, &$approvedCount, &$billedCount) {
                $oldValues = ['action_status' => $reading->action_status];

                // اعتماد القراءة أولاً
                $reading->update(['action_status' => MeterReading::ACTION_STATUS_APPROVED]);

                AuditLog::log(
                    'approve',
                    $reading,
                    $user,
                    $oldValues,
                    ['action_status' => MeterReading::ACTION_STATUS_APPROVED],
                    'اعتماد قراءة العداد: ' . $reading->reading_number
                );

                // ترحيل تلقائي للفوترة: إنشاء فاتورة مسودة إذا لم تكن موجودة
                if (!$reading->invoice()->exists()) {
                    Invoice::createFromReading($reading, $user->id);

                    AuditLog::log(
                        'create',
                        $reading,
                        $user,
                        [],
                        [],
                        'ترحيل قراءة للفوترة تلقائياً: ' . $reading->reading_number
                    );

                    $billedCount++;
                }

                $approvedCount++;
            });
        }

        $message = "تم اعتماد {$approvedCount} قراءة وترحيل {$billedCount} منها إلى شاشة الفوترة.";
        if ($abnormalCount > 0) {
            $message .= " تم تخطي {$abnormalCount} قراءة غير طبيعية (تحتاج اعتماداً منفرداً مع ذكر السبب).";
        }
        if ($skippedCount > 0) {
            $message .= " تم تخطي {$skippedCount} قراءة (معتمدة أو مفوترة مسبقاً).";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'approved' => $approvedCount, 'skipped' => $skippedCount, 'abnormal' => $abnormalCount]);
        }

        return redirect()->route('admin.meter-readings.index')->with('success', $message);
    }

    /**
     * Approve a single abnormal meter reading with a mandatory reason.
     */
    public function approveAbnormal(Request $request, MeterReading $meterReading): JsonResponse
    {
        $this->authorize('create', MeterReading::class);

        if (!$meterReading->isApprovable()) {
            return response()->json(['success' => false, 'message' => 'القراءة معتمدة أو مفوترة مسبقاً.']);
        }

        if (!$meterReading->isAbnormal()) {
            return response()->json(['success' => false, 'message' => 'هذه القراءة طبيعية، استخدم الاعتماد الجماعي.']);
        }

        $request->validate([
            'abnormal_reason' => 'required|string|min:5|max:1000',
        ], [
            'abnormal_reason.required' => 'سبب اعتماد القراءة غير الطبيعية إلزامي.',
            'abnormal_reason.min'      => 'يجب أن لا يقل السبب عن 5 أحرف.',
        ]);

        $user = auth()->user();

        DB::transaction(function () use ($meterReading, $request, $user) {
            $oldValues = ['action_status' => $meterReading->action_status];

            $meterReading->update([
                'action_status'   => MeterReading::ACTION_STATUS_APPROVED,
                'abnormal_reason' => $request->input('abnormal_reason'),
                'approved_by'     => $user->id,
                'approved_at'     => now(),
            ]);

            AuditLog::log(
                'approve',
                $meterReading,
                $user,
                $oldValues,
                ['action_status' => MeterReading::ACTION_STATUS_APPROVED, 'abnormal_reason' => $request->input('abnormal_reason')],
                'اعتماد قراءة غير طبيعية: ' . $meterReading->reading_number
            );

            // ترحيل تلقائي للفوترة
            if (!$meterReading->invoice()->exists()) {
                Invoice::createFromReading($meterReading, $user->id);

                AuditLog::log(
                    'create',
                    $meterReading,
                    $user,
                    [],
                    [],
                    'ترحيل قراءة غير طبيعية للفوترة: ' . $meterReading->reading_number
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم اعتماد القراءة غير الطبيعية وترحيلها إلى شاشة الفوترة.',
        ]);
    }

    /**
     * Get subscribers by operator (AJAX for filter Select2)
     */
    public function getSubscribersByOperator(Request $request): JsonResponse
    {
        $operatorId = (int) $request->input('operator_id', 0);

        $query = Subscriber::select('id', 'subscription_number', 'subscriber_name');

        if ($operatorId > 0) {
            $query->whereHas('generationUnits', function($q) use ($operatorId) {
                $q->where('operator_id', $operatorId);
            });
        }

        $subscribers = $query->orderBy('subscription_number')->limit(200)->get();

        return response()->json([
            'success' => true,
            'subscribers' => $subscribers,
        ]);
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
                'previous_reading' => $subscriber->opening_reading ?? 0,
                'reading_date' => null,
                'meter_number' => $subscriber->meter_number ?? '',
            ],
            'subscriber' => [
                'meter_number' => $subscriber->meter_number ?? '',
                'opening_reading' => $subscriber->opening_reading ?? 0,
            ]
        ]);
    }
}
