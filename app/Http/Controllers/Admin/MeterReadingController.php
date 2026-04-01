<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMeterReadingRequest;
use App\Http\Requests\Admin\UpdateMeterReadingRequest;
use App\Models\AuditLog;
use App\Models\GenerationUnit;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Subscriber;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MeterReadingController extends Controller
{
    /**
     * بناء query فلترة القراءات المشترك بين index و export
     */
    private function buildIndexQuery(Request $request): array
    {
        $user = auth()->user();
        $query = MeterReading::with(['subscriber', 'creator', 'updater']);

        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $query->whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $currentOperator->id));
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $opIds = $operators->pluck('id')->toArray();
                $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $opIds));
                $currentOperator = $operators->first();
            }
        }

        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
        if ($canSelectOperator && (int) $request->input('operator_id', 0) > 0) {
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $request->operator_id));
        }

        if ((int) $request->input('subscriber_id', 0) > 0) {
            $query->where('subscriber_id', $request->subscriber_id);
        }

        if ($request->filled('reading_status') && in_array($request->input('reading_status'), ['1', '2'])) {
            $query->where('reading_status', $request->input('reading_status'));
        }

        if ($request->filled('action_status') && in_array($request->input('action_status'), ['0', '1', '2'])) {
            $query->where('action_status', $request->input('action_status'));
        }

        if ($request->filled('box_number')) {
            $query->whereHas('subscriber', fn($q) => $q->where('box_number', $request->box_number));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('reading_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('reading_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reading_number', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%")
                  ->orWhereHas('subscriber', fn($q2) => $q2->where('subscription_number', 'like', "%{$search}%")->orWhere('subscriber_name', 'like', "%{$search}%"));
            });
        }

        return [$query, $currentOperator, $canSelectOperator];
    }

    /**
     * Display a listing of meter readings.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', MeterReading::class);

        [$query, $currentOperator, $canSelectOperator] = $this->buildIndexQuery($request);
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
        } elseif ($canSelectOperator && ($operatorId = (int) $request->input('operator_id', 0)) > 0) {
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
    /**
     * تصدير القراءات إلى Excel حسب الفلاتر
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', MeterReading::class);

        [$query] = $this->buildIndexQuery($request);
        $readings = $query->latest('reading_date')->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        $headers = [
            'رقم القراءة', 'رقم الاشتراك', 'اسم المشترك', 'رقم الصندوق',
            'رقم العداد', 'القراءة السابقة', 'القراءة الحالية', 'الاستهلاك (kWh)',
            'تاريخ القراءة', 'الفترة (يوم)', 'حالة القراءة', 'حالة الإجراء',
        ];
        $columns = range('A', 'L');

        foreach ($headers as $idx => $header) {
            $cell = $columns[$idx] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        foreach ($readings as $r) {
            $readingStatusLabel = $r->reading_status == 1 ? 'طبيعية' : 'غير طبيعية';
            $actionLabels = [0 => 'غير معتمدة', 1 => 'معتمدة', 2 => 'مفوترة'];
            $actionLabel = $actionLabels[$r->action_status] ?? '—';

            $sheet->setCellValue('A' . $row, $r->reading_number);
            $sheet->setCellValue('B' . $row, $r->subscriber?->subscription_number ?? '');
            $sheet->setCellValue('C' . $row, $r->subscriber?->subscriber_name ?? '');
            $sheet->setCellValue('D' . $row, $r->subscriber?->box_number ?? '');
            $sheet->setCellValue('E' . $row, $r->meter_number ?? '');
            $sheet->setCellValue('F' . $row, $r->previous_reading);
            $sheet->setCellValue('G' . $row, $r->current_reading);
            $sheet->setCellValue('H' . $row, $r->consumption_kwh);
            $sheet->setCellValue('I' . $row, $r->reading_date?->format('Y-m-d') ?? '');
            $sheet->setCellValue('J' . $row, $r->consumption_period_days);
            $sheet->setCellValue('K' . $row, $readingStatusLabel);
            $sheet->setCellValue('L' . $row, $actionLabel);

            foreach ($columns as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $row++;
        }

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'قراءات_العدادات_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (!file_exists(storage_path('app/temp'))) mkdir(storage_path('app/temp'), 0755, true);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

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
            $subscribers = collect(); // تُحمّل عبر AJAX بعد اختيار المشغل
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

                // ترحيل تلقائي للفوترة: إنشاء فاتورة مسودة إذا لم تكن موجودة (بدون المحذوفة)
                if (!$reading->invoice()->whereNull('invoices.deleted_at')->exists()) {
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
            $message .= " تم تخطي {$abnormalCount} قراءة غير طبيعية (تحتاج تعديل أو حذف وإعادة إدخال).";
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
     * بناء query القراءات حسب الفلاتر (مشترك بين approve و preview)
     */
    private function buildFilteredReadingsQuery(Request $request, bool $pendingOnly = true)
    {
        $user = auth()->user();
        $query = MeterReading::with('subscriber')->where('action_status', MeterReading::ACTION_STATUS_PENDING);

        if ($user->isCompanyOwner()) {
            $opIds = $user->ownedOperators()->pluck('id')->toArray();
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $opIds));
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $opIds = $user->operators->pluck('id')->toArray();
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $opIds));
        } elseif ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $operatorId));
            }
        }

        if ((int) $request->input('subscriber_id', 0) > 0) {
            $query->where('subscriber_id', $request->subscriber_id);
        }
        if ($request->filled('box_number')) {
            $query->whereHas('subscriber', fn($q) => $q->where('box_number', $request->box_number));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('reading_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('reading_date', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * اعتماد القراءات حسب الفلاتر — خطوة 1: اعتماد الطبيعية + إرجاع غير الطبيعية
     */
    public function bulkApproveByFilter(Request $request): JsonResponse
    {
        $this->authorize('create', MeterReading::class);

        $user = auth()->user();
        $query = $this->buildFilteredReadingsQuery($request);
        $allReadings = $query->get();

        if ($allReadings->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'لا توجد قراءات مطابقة للاعتماد.']);
        }

        $normal = $allReadings->where('reading_status', MeterReading::READING_STATUS_NORMAL);
        $abnormal = $allReadings->where('reading_status', MeterReading::READING_STATUS_ABNORMAL);

        // اعتماد الطبيعية
        $approvedCount = 0;
        $billedCount = 0;

        foreach ($normal as $reading) {
            DB::transaction(function () use ($reading, $user, &$approvedCount, &$billedCount) {
                $reading->update(['action_status' => MeterReading::ACTION_STATUS_APPROVED]);
                AuditLog::log('approve', $reading, $user,
                    ['action_status' => MeterReading::ACTION_STATUS_PENDING],
                    ['action_status' => MeterReading::ACTION_STATUS_APPROVED],
                    'اعتماد قراءة (جماعي بالفلتر): ' . $reading->reading_number
                );
                if (!$reading->invoice()->whereNull('invoices.deleted_at')->exists()) {
                    Invoice::createFromReading($reading, $user->id);
                    $billedCount++;
                }
                $approvedCount++;
            });
        }

        // إرجاع غير الطبيعية للمودال
        $abnormalList = $abnormal->map(fn($r) => [
            'id' => $r->id,
            'reading_number' => $r->reading_number,
            'subscriber_name' => $r->subscriber?->subscriber_name ?? '—',
            'subscription_number' => $r->subscriber?->subscription_number ?? '—',
            'consumption_kwh' => (float) $r->consumption_kwh,
            'reading_date' => $r->reading_date?->format('Y-m-d'),
        ])->values();

        $message = "تم اعتماد {$approvedCount} قراءة طبيعية وترحيل {$billedCount} منها للفوترة.";
        if ($abnormal->count() > 0) {
            $message .= " يوجد {$abnormal->count()} قراءة غير طبيعية تحتاج تعديل أو حذف وإعادة إدخال.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'approved' => $approvedCount,
            'abnormal_count' => $abnormal->count(),
            'abnormal_readings' => [], // No longer needed - abnormal readings need editing, not approval
        ]);
    }

    /**
     * اعتماد القراءات غير الطبيعية مع الأسباب (جماعي)
     */
    public function bulkApproveAbnormal(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن اعتماد القراءات غير الطبيعية. يجب تعديلها أو حذفها وإعادة إدخالها.',
        ], 422);
    }

    /**
     * Approve a single abnormal meter reading with a mandatory reason.
     */
    public function approveAbnormal(Request $request, MeterReading $meterReading): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن اعتماد قراءة غير طبيعية. يجب تعديل القراءة أو حذفها وإعادة إدخالها بشكل صحيح.',
        ], 422);
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
    // ===== كشف القراءات الجماعية =====

    /**
     * شاشة كشف القراءات الجماعية
     */
    public function bulkCreate(): View
    {
        $this->authorize('create', MeterReading::class);

        $user = auth()->user();
        $operator = null;
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
            $generationUnits = collect(); // loaded via AJAX after operator selection
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            $operatorIds = $user->ownedOperators()->pluck('id');
            $generationUnits = GenerationUnit::whereIn('operator_id', $operatorIds)
                ->with('operator:id,name')->select('id', 'name', 'unit_code', 'operator_id')->orderBy('name')->get();
        } else {
            $operator = $user->operators()->first();
            $operatorIds = $user->operators()->pluck('operators.id');
            $generationUnits = GenerationUnit::whereIn('operator_id', $operatorIds)
                ->with('operator:id,name')->select('id', 'name', 'unit_code', 'operator_id')->orderBy('name')->get();
        }

        return view('admin.meter-readings.bulk-create', compact('generationUnits', 'operators', 'operator'));
    }

    /**
     * جلب المشتركين للكشف الجماعي (AJAX)
     */
    public function bulkCreateFetch(Request $request): JsonResponse
    {
        $this->authorize('create', MeterReading::class);

        $request->validate([
            'generation_unit_id' => 'required_without:box_number|nullable|exists:generation_units,id',
            'box_number' => 'required_without:generation_unit_id|nullable|string',
            'reading_date' => 'required|date',
        ]);

        $query = Subscriber::with('generationUnits')
            ->where('subscription_status', 1); // نشطين فقط

        if ($request->filled('generation_unit_id')) {
            $query->whereHas('generationUnits', fn($q) => $q->where('generation_units.id', $request->generation_unit_id));
        }

        if ($request->filled('box_number')) {
            $query->where('box_number', $request->box_number);
        }

        $subscribers = $query
            ->orderByRaw("CASE WHEN box_number IS NULL OR box_number = '' THEN 1 ELSE 0 END")
            ->orderByRaw('CAST(box_number AS UNSIGNED)')
            ->orderBy('subscription_number')
            ->get();

        $readingDate = $request->reading_date;
        $rows = [];
        $seq = 1;
        $datePrefix = date('Ymd', strtotime($readingDate)) . '-';

        foreach ($subscribers as $sub) {
            $lastReading = MeterReading::getLastReadingForSubscriber($sub->id);

            // تخطي المشتركين اللي عندهم قراءة بنفس التاريخ أو بعده
            if ($lastReading && $lastReading->reading_date->format('Y-m-d') >= $readingDate) {
                continue;
            }

            $rows[] = [
                'subscriber_id' => $sub->id,
                'reading_number' => $datePrefix . str_pad($seq++, 5, '0', STR_PAD_LEFT),
                'box_number' => $sub->box_number ?? '',
                'subscription_number' => $sub->subscription_number,
                'subscriber_name' => $sub->subscriber_name,
                'meter_number' => $sub->meter_number ?? '',
                'previous_reading' => $lastReading ? (float) $lastReading->current_reading : (float) ($sub->opening_reading ?? 0),
                'previous_reading_date' => $lastReading ? $lastReading->reading_date->format('Y-m-d') : null,
            ];
        }

        return response()->json(['success' => true, 'rows' => $rows, 'count' => count($rows)]);
    }

    /**
     * حفظ القراءات الجماعية
     */
    public function bulkCreateStore(Request $request): JsonResponse
    {
        $this->authorize('create', MeterReading::class);

        $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.subscriber_id' => 'required|exists:subscribers,id',
            'readings.*.current_reading' => 'required|numeric|min:0',
            'readings.*.reading_date' => 'required|date|before_or_equal:today',
        ]);

        $saved = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$saved, &$errors) {
            foreach ($request->readings as $i => $row) {
                try {
                    $subscriber = Subscriber::find($row['subscriber_id']);
                    if (!$subscriber || $subscriber->subscription_status != 1) {
                        $errors[] = "صف " . ($i + 1) . ": المشترك غير نشط";
                        continue;
                    }

                    $lastReading = MeterReading::getLastReadingForSubscriber($subscriber->id);
                    $previousReading = $lastReading ? (float) $lastReading->current_reading : (float) ($subscriber->opening_reading ?? 0);
                    $currentReading = (float) $row['current_reading'];
                    $consumption = $currentReading - $previousReading;

                    // حساب فترة الاستهلاك
                    $previousDate = $lastReading ? $lastReading->reading_date : $subscriber->subscription_date;
                    $periodDays = $previousDate ? \Carbon\Carbon::parse($previousDate)->diffInDays(\Carbon\Carbon::parse($row['reading_date'])) + 1 : 1;

                    $readingNumber = MeterReading::generateReadingNumber($subscriber->id);

                    MeterReading::create([
                        'reading_number' => $readingNumber,
                        'subscriber_id' => $subscriber->id,
                        'meter_number' => $subscriber->meter_number ?? '',
                        'previous_reading' => $previousReading,
                        'current_reading' => $currentReading,
                        'consumption_kwh' => $consumption,
                        'reading_date' => $row['reading_date'],
                        'consumption_period_days' => $periodDays,
                        'previous_reading_date' => $previousDate ? \Carbon\Carbon::parse($previousDate)->format('Y-m-d') : null,
                        'reading_status' => MeterReading::determineReadingStatus($consumption),
                        'action_status' => MeterReading::ACTION_STATUS_PENDING,
                        'created_by' => auth()->id(),
                    ]);

                    $saved++;
                } catch (\Exception $e) {
                    $errors[] = "صف " . ($i + 1) . ": " . $e->getMessage();
                }
            }
        });

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'errors' => $errors,
            'message' => "تم حفظ {$saved} قراءة بنجاح" . (count($errors) > 0 ? " ({" . count($errors) . "} أخطاء)" : ''),
        ]);
    }

    /**
     * تصدير بيانات الكشف إلى Excel
     */
    public function bulkCreateExport(Request $request)
    {
        $this->authorize('create', MeterReading::class);

        $rows = $request->input('rows', []);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        $headers = ['رقم القراءة', 'رقم الصندوق', 'رقم الاشتراك', 'اسم المشترك', 'القراءة السابقة', 'تاريخها', 'القراءة الحالية', 'تاريخها', 'الاستهلاك'];
        $columns = range('A', 'I');

        foreach ($headers as $idx => $header) {
            $cell = $columns[$idx] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        $totalConsumption = 0;
        foreach ($rows as $r) {
            $consumption = ((float)($r['current_reading'] ?? 0)) - ((float)($r['previous_reading'] ?? 0));
            $totalConsumption += max(0, $consumption);

            $sheet->setCellValue('A' . $row, $r['reading_number'] ?? '');
            $sheet->setCellValue('B' . $row, $r['box_number'] ?? '');
            $sheet->setCellValue('C' . $row, $r['subscription_number'] ?? '');
            $sheet->setCellValue('D' . $row, $r['subscriber_name'] ?? '');
            $sheet->setCellValue('E' . $row, $r['previous_reading'] ?? 0);
            $sheet->setCellValue('F' . $row, $r['previous_reading_date'] ?? '');
            $sheet->setCellValue('G' . $row, $r['current_reading'] ?? '');
            $sheet->setCellValue('H' . $row, $r['reading_date'] ?? '');
            $sheet->setCellValue('I' . $row, $consumption > 0 ? $consumption : '');

            foreach ($columns as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $row++;
        }

        // صف الإجمالي
        $sheet->setCellValue('H' . $row, 'إجمالي الاستهلاك');
        $sheet->setCellValue('I' . $row, $totalConsumption);
        $sheet->getStyle('H' . $row . ':I' . $row)->getFont()->setBold(true);

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'كشف_قراءات_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (!file_exists(storage_path('app/temp'))) mkdir(storage_path('app/temp'), 0755, true);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

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

        $excludeId = (int) $request->input('exclude_id', 0);
        $query = MeterReading::where('subscriber_id', $subscriberId)
            ->orderBy('reading_date', 'desc')
            ->orderBy('id', 'desc');
        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }
        $lastReading = $query->first();

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
