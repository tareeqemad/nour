<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvoiceRequest;
use App\Http\Requests\Admin\UpdateInvoiceRequest;
use App\Models\AuditLog;
use App\Models\ElectricityTariffPrice;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Operator;
use App\Models\Setting;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * قائمة الفواتير
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $user  = auth()->user();
        $query = Invoice::with(['subscriber', 'meterReading', 'creator'])
                           ->withSum('payments', 'amount_paid');

        // تحديد نطاق المشغل
        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $query->whereHas('subscriber.generationUnits', fn($q) =>
                    $q->where('operator_id', $currentOperator->id));
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $ids = $operators->pluck('id')->toArray();
                $query->whereHas('subscriber.generationUnits', fn($q) =>
                    $q->whereIn('operator_id', $ids));
                $currentOperator = $operators->first();
            }
        }

        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();

        // فلاتر
        if ($canSelectOperator && ($oid = (int) $request->input('operator_id', 0)) > 0) {
            $query->whereHas('subscriber.generationUnits', fn($q) =>
                $q->where('operator_id', $oid));
        }

        if ($sid = (int) $request->input('subscriber_id', 0)) {
            $query->where('subscriber_id', $sid);
        }

        if ($request->filled('invoice_status')) {
            $st = $request->input('invoice_status');
            if ($st === '5') {
                // متأخرة: جديدة + تجاوزت تاريخ الاستحقاق
                $query->where('invoice_status', Invoice::STATUS_ISSUED)
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', now());
            } elseif (in_array($st, ['0','1','2','3','4'])) {
                if ($st == '1') {
                    // جديدة: مصدرة ولم تتجاوز تاريخ الاستحقاق
                    $query->where('invoice_status', Invoice::STATUS_ISSUED)
                          ->where(fn($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', now()));
                } else {
                    $query->where('invoice_status', $st);
                }
            }
        }

        if ($df = $request->input('date_from', '')) {
            $query->whereDate('invoice_date', '>=', $df);
        }
        if ($dt = $request->input('date_to', '')) {
            $query->whereDate('invoice_date', '<=', $dt);
        }

        if ($s = $request->input('search', '')) {
            $query->where(function($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('subscriber', fn($q2) =>
                      $q2->where('subscription_number', 'like', "%{$s}%")
                         ->orWhere('subscriber_name', 'like', "%{$s}%"));
            });
        }

        $invoices = $query->latest('invoice_date')->latest()->paginate(200);

        if ($request->ajax() || $request->wantsJson()) {
            $page   = (int) $request->input('page', 1);
            $append = $page > 1;
            $html   = view('admin.invoices.partials.list', compact('invoices', 'append'))->render();
            return response()->json([
                'success'   => true,
                'html'      => $html,
                'count'     => $invoices->total(),
                'has_more'  => $invoices->hasMorePages(),
                'next_page' => $invoices->hasMorePages() ? $page + 1 : null,
                'append'    => $append,
            ]);
        }

        $operators  = $canSelectOperator ? Operator::select('id','name')->orderBy('name')->get() : collect();
        $subscribers = collect();
        if ($currentOperator) {
            $subscribers = Subscriber::whereHas('generationUnits', fn($q) =>
                $q->where('operator_id', $currentOperator->id))
                ->select('id','subscription_number','subscriber_name')
                ->orderBy('subscription_number')->get();
        }

        return view('admin.invoices.index', compact('invoices','operators','subscribers','currentOperator','canSelectOperator'));
    }

    /**
     * نموذج إنشاء فاتورة جديدة
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $user = auth()->user();

        // جلب القراءات المعتمدة وغير المفوترة
        $readingQuery = MeterReading::with('subscriber')
            ->where('action_status', MeterReading::ACTION_STATUS_APPROVED)
            ->whereDoesntHave('invoice');

        if ($user->isCompanyOwner()) {
            $op = $user->ownedOperators()->first();
            if ($op) {
                $readingQuery->whereHas('subscriber.generationUnits', fn($q) =>
                    $q->where('operator_id', $op->id));
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $ids = $user->operators()->pluck('operators.id')->toArray();
            if ($ids) {
                $readingQuery->whereHas('subscriber.generationUnits', fn($q) =>
                    $q->whereIn('operator_id', $ids));
            }
        }

        $readings = $readingQuery->orderBy('reading_date')->get();

        // سعر التعرفة النشط
        $activeTariff = ElectricityTariffPrice::getActivePriceForDate(now());

        // الحد الأدنى من الإعدادات
        $minimumCharge = (float) Setting::get('invoice_minimum_charge', 0);

        // إذا تم تمرير reading_id مسبقاً
        $selectedReadingId = (int) $request->input('reading_id', 0);
        $selectedReading   = $selectedReadingId ? MeterReading::with('subscriber')->find($selectedReadingId) : null;

        return view('admin.invoices.create', compact(
            'readings', 'activeTariff', 'minimumCharge', 'selectedReading'
        ));
    }

    /**
     * حفظ فاتورة جديدة
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['invoice_status'] = Invoice::STATUS_DRAFT;
        $data['invoice_number'] = null; // رقم الفاتورة يُنشأ عند الإصدار النهائي فقط
        $data['created_by']     = auth()->id();

        $invoice = Invoice::create($data);

        AuditLog::log('create', $invoice, auth()->user(), [], $data, 'إنشاء فاتورة مسودة');

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'تم إنشاء الفاتورة كمسودة بنجاح.');
    }

    /**
     * عرض فاتورة
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['subscriber.generationUnits.operator', 'meterReading', 'creator', 'updater', 'issuer']);
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * طباعة فاتورة (صفحة طباعة مستقلة)
     */
    public function print(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);
        $invoice->load(['subscriber.generationUnits.operator', 'meterReading', 'creator', 'updater', 'issuer', 'payments']);
        return view('admin.invoices.print', compact('invoice'));
    }

    /**
     * تصدير فاتورة PDF
     */
    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['subscriber.generationUnits.operator', 'meterReading', 'creator', 'updater', 'issuer', 'payments']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.print', [
                'invoice' => $invoice,
                'isPdf' => true,
            ])
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $fileName = 'فاتورة_' . ($invoice->invoice_number ?? 'مسودة') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * نموذج تعديل فاتورة (مسودة فقط)
     */
    public function edit(Invoice $invoice): View|RedirectResponse
    {
        $this->authorize('update', $invoice);
        $invoice->load(['subscriber.generationUnits', 'meterReading']);
        $activeTariff  = ElectricityTariffPrice::getActivePriceForDate(now());
        $minimumCharge = (float) Setting::get('invoice_minimum_charge', 0);
        return view('admin.invoices.edit', compact('invoice', 'activeTariff', 'minimumCharge'));
    }

    /**
     * تحديث فاتورة
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $old  = $invoice->toArray();
        $data = $request->validated();
        $data['last_updated_by'] = auth()->id();

        // تطبيق قواعد الخصم والحد الأدنى
        $subscriber = $invoice->subscriber;
        if ($subscriber) {
            $invoiceDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : null;
            [$data['discount_rate'], $data['minimum_charge']] = Invoice::applySubscriberRules(
                $subscriber,
                (float)($data['discount_rate'] ?? $invoice->discount_rate),
                (float)($data['minimum_charge'] ?? $invoice->minimum_charge),
                $invoiceDate
            );
        }

        // إعادة احتساب القيم من جهة السيرفر
        $amounts = Invoice::calculateAmounts(
            (float)($data['consumption_kwh'] ?? $invoice->consumption_kwh),
            (float)($data['price_per_kwh'] ?? $invoice->price_per_kwh),
            (float)$data['discount_rate'],
            (float)$data['minimum_charge'],
            (float)($data['previous_balance'] ?? $invoice->previous_balance)
        );
        $data['invoice_amount'] = $amounts['invoice_amount'];
        $data['total_amount']   = $amounts['total_amount'];

        $invoice->update($data);

        AuditLog::log('update', $invoice, auth()->user(), $old, $data, 'تعديل فاتورة: ' . $invoice->invoice_number);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'تم تحديث الفاتورة بنجاح.');
    }

    /**
     * حذف فاتورة (مسودة فقط)
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        // إعادة حالة القراءة إلى معتمدة
        if ($invoice->meter_reading_id) {
            MeterReading::where('id', $invoice->meter_reading_id)
                ->update(['action_status' => MeterReading::ACTION_STATUS_APPROVED]);
        }

        AuditLog::log('delete', $invoice, auth()->user(), $invoice->toArray(), [], 'حذف فاتورة: ' . $invoice->invoice_number);
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'تم حذف الفاتورة.');
    }

    /**
     * إصدار فاتورة (من مسودة إلى جديدة)
     */
    public function issue(Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorize('issue', $invoice);

        // ضابط #1: لا يُسمح بإصدار فاتورة ما لم تكن القراءة المرتبطة معتمدة
        if ($invoice->meter_reading_id) {
            $reading = MeterReading::find($invoice->meter_reading_id);
            if (!$reading || !in_array($reading->action_status, [
                MeterReading::ACTION_STATUS_APPROVED,
                MeterReading::ACTION_STATUS_BILLED,
            ])) {
                $msg = 'لا يمكن إصدار الفاتورة: القراءة المرتبطة غير معتمدة.';
                if (request()->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withErrors(['reading' => $msg]);
            }
        }

        // التحقق من أن المشترك نشط
        $subscriber = $invoice->subscriber;
        if (!$subscriber || $subscriber->subscription_status != 1) {
            return redirect()->back()->with('error', 'لا يمكن إصدار فاتورة لمشترك غير نشط.');
        }

        // التحقق من ارتباط المشترك بوحدة توليد
        if ($subscriber->generationUnits()->doesntExist()) {
            return redirect()->back()->with('error', 'المشترك غير مرتبط بوحدة توليد. يرجى ربطه أولاً.');
        }

        // التحقق من وجود تعرفة نشطة
        $tariff = ElectricityTariffPrice::getActivePriceForDate(
            $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : now()
        );
        if (!$tariff) {
            return redirect()->back()->with('error', 'لا توجد تعرفة كهرباء نشطة. يرجى إضافة تعرفة أولاً.');
        }

        // generateInvoiceNumber() تستخدم INSERT...ON DUPLICATE KEY UPDATE الذرية
        // يجب أن تكون داخل DB::transaction لضمان rollback التسلسل عند الفشل
        DB::transaction(function () use ($invoice) {
            // Lock the invoice row and refresh to prevent concurrent issuance
            $invoice = Invoice::lockForUpdate()->find($invoice->id);

            // Re-check it's still a draft (another user might have issued it)
            if ($invoice->invoice_status !== Invoice::STATUS_DRAFT) {
                throw new \Exception('الفاتورة تم إصدارها بالفعل من مستخدم آخر.');
            }

            $old = $invoice->only(['invoice_status', 'previous_balance', 'total_amount']);

            $invoiceNumber = $invoice->invoice_number
                ?: Invoice::generateInvoiceNumber();

            // تطبيق قواعد الخصم والحد الأدنى من بيانات المشترك
            $subscriber = $invoice->subscriber;
            $invoiceDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : null;
            [$discount, $minCharge] = $subscriber
                ? Invoice::applySubscriberRules($subscriber, (float)$invoice->discount_rate, (float)$invoice->minimum_charge, $invoiceDate)
                : [(float)$invoice->discount_rate, (float)$invoice->minimum_charge];

            // إعادة احتساب الرصيد السابق التراكمي لحظة الإصدار
            $freshPrevBalance = Invoice::calculatePreviousBalance($invoice->subscriber_id, $invoice->id);

            // إعادة احتساب إجمالي الفاتورة بالرصيد المحدّث
            $freshAmounts = Invoice::calculateAmounts(
                (float)$invoice->consumption_kwh,
                (float)$invoice->price_per_kwh,
                $discount,
                $minCharge,
                $freshPrevBalance
            );

            $issuedAt = now();
            $invoice->update([
                'invoice_status'   => Invoice::STATUS_ISSUED,
                'invoice_number'   => $invoiceNumber,
                'discount_rate'    => $discount,
                'minimum_charge'   => $minCharge,
                'previous_balance' => round($freshPrevBalance, 2),
                'invoice_amount'   => $freshAmounts['invoice_amount'],
                'total_amount'     => $freshAmounts['total_amount'],
                'issued_by'        => auth()->id(),
                'issued_at'        => $issuedAt,
                'due_date'         => $invoice->due_date ?? $issuedAt->copy()->addDays(7),
                'last_updated_by'  => auth()->id(),
            ]);

            // تحديث حالة القراءة إلى مفوترة عند الإصدار
            if ($invoice->meter_reading_id) {
                MeterReading::where('id', $invoice->meter_reading_id)
                    ->update(['action_status' => MeterReading::ACTION_STATUS_BILLED]);
            }

            AuditLog::log(
                'update',
                $invoice,
                auth()->user(),
                $old,
                ['invoice_status' => Invoice::STATUS_ISSUED, 'invoice_number' => $invoiceNumber],
                'إصدار فاتورة نهائي: ' . $invoiceNumber
            );
        });

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم إصدار الفاتورة بنجاح.']);
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'تم إصدار الفاتورة بنجاح.');
    }

    /**
     * إلغاء فاتورة
     */
    public function cancel(Request $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorize('cancel', $invoice);

        if (!$invoice->isCancellable()) {
            $msg = 'لا يمكن إلغاء هذه الفاتورة.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : redirect()->back()->with('error', $msg);
        }

        try {
            DB::transaction(function () use ($invoice, $request) {
                $isDraft = $invoice->invoice_status === Invoice::STATUS_DRAFT;
                $cancelReason = $request->input('cancel_reason', '');

                // Reset reading to PENDING (unapproved) so it can be re-edited
                if ($invoice->meter_reading_id) {
                    $reading = $invoice->meterReading;
                    if ($reading) {
                        $reading->update(['action_status' => MeterReading::ACTION_STATUS_PENDING]);
                    }
                }

                if ($isDraft) {
                    // Draft: delete the invoice entirely
                    AuditLog::log(
                        'delete',
                        $invoice,
                        auth()->user(),
                        $invoice->toArray(),
                        ['status' => 'deleted', 'reason' => $cancelReason],
                        "حذف فاتورة مسودة للمشترك رقم {$invoice->subscriber_id}"
                    );

                    $invoice->forceDelete();
                } else {
                    $old = $invoice->only(['invoice_status', 'notes']);

                    // Issued/Overdue: mark as cancelled + create reverse entry
                    $invoice->update([
                        'invoice_status' => Invoice::STATUS_CANCELLED,
                        'last_updated_by' => auth()->id(),
                        'notes' => trim(($invoice->notes ? $invoice->notes . "\n" : '') . 'سبب الإلغاء: ' . $cancelReason),
                    ]);

                    // Create reverse financial entry
                    \App\Models\SubscriberAccountEntry::create([
                        'subscriber_id' => $invoice->subscriber_id,
                        'invoice_id' => $invoice->id,
                        'entry_type' => \App\Models\SubscriberAccountEntry::TYPE_REVERSE,
                        'amount' => -$invoice->invoice_amount,
                        'description' => "إلغاء فاتورة رقم {$invoice->invoice_number} - السبب: {$cancelReason}",
                        'created_by' => auth()->id(),
                    ]);

                    // Refresh drafts for this subscriber
                    Invoice::refreshDraftsForSubscriber($invoice->subscriber_id);

                    AuditLog::log(
                        'update',
                        $invoice,
                        auth()->user(),
                        $old,
                        ['invoice_status' => Invoice::STATUS_CANCELLED, 'reason' => $cancelReason],
                        "إلغاء فاتورة رقم {$invoice->invoice_number} - السبب: {$cancelReason}"
                    );
                }
            });

            $msg = $invoice->invoice_status === Invoice::STATUS_DRAFT
                ? 'تم حذف الفاتورة المسودة بنجاح.'
                : 'تم إلغاء الفاتورة بنجاح.';

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $msg])
                : redirect()->route('admin.invoices.index')->with('success', $msg);
        } catch (\Exception $e) {
            $msg = 'حدث خطأ أثناء إلغاء الفاتورة.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Bulk issue all draft invoices
     */
    public function bulkIssue(Request $request): JsonResponse
    {
        $request->validate([
            'price_per_kwh' => 'required|numeric|min:0',
            'confirm_minimum_charge' => 'required|boolean',
        ], [
            'price_per_kwh.required' => 'قيمة التعرفة مطلوبة.',
            'confirm_minimum_charge.required' => 'يجب إقرار الحد الأدنى.',
        ]);

        $user = auth()->user();
        $query = Invoice::where('invoice_status', Invoice::STATUS_DRAFT)
            ->whereNotNull('meter_reading_id');

        // Scope by operator for non-admin users
        if ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id')->toArray();
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $operatorIds));
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operatorIds = $user->operators()->pluck('operators.id')->toArray();
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $operatorIds));
        }

        $drafts = $query->with(['subscriber', 'meterReading'])->lockForUpdate()->get();

        if ($drafts->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'لا توجد فواتير مسودة للإصدار.']);
        }

        $issuedCount = 0;
        $errors = [];

        DB::transaction(function () use ($drafts, $request, &$issuedCount, &$errors) {
            foreach ($drafts as $invoice) {
                try {
                    // Skip if already issued by another user
                    if ($invoice->invoice_status !== Invoice::STATUS_DRAFT) {
                        continue;
                    }

                    $reading = $invoice->meterReading;
                    if (!$reading || !in_array($reading->action_status, [MeterReading::ACTION_STATUS_APPROVED, MeterReading::ACTION_STATUS_BILLED])) {
                        $errors[] = "فاتورة المشترك {$invoice->subscriber->subscriber_name}: القراءة غير معتمدة";
                        continue;
                    }

                    $subscriber = $invoice->subscriber;

                    if (!$subscriber || $subscriber->subscription_status != 1) {
                        $errors[] = "المشترك {$subscriber?->subscriber_name}: غير نشط";
                        continue;
                    }

                    if ($subscriber->generationUnits()->doesntExist()) {
                        $errors[] = "المشترك {$subscriber->subscriber_name}: غير مرتبط بوحدة توليد";
                        continue;
                    }

                    // Apply subscriber rules fresh
                    [$discount, $minCharge] = Invoice::applySubscriberRules(
                        $subscriber,
                        $invoice->discount_rate,
                        $invoice->minimum_charge,
                        $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : now()
                    );

                    // Recalculate with fresh previous balance
                    $prevBalance = Invoice::calculatePreviousBalance($subscriber->id, $invoice->id);
                    $amounts = Invoice::calculateAmounts(
                        $invoice->consumption_kwh,
                        $request->price_per_kwh,
                        $discount,
                        $minCharge,
                        $prevBalance
                    );

                    // Generate invoice number
                    $invoiceNumber = Invoice::generateInvoiceNumber();

                    $invoice->update([
                        'invoice_number' => $invoiceNumber,
                        'invoice_status' => Invoice::STATUS_ISSUED,
                        'price_per_kwh' => $request->price_per_kwh,
                        'discount_rate' => $discount,
                        'minimum_charge' => $minCharge,
                        'previous_balance' => $prevBalance,
                        'invoice_amount' => $amounts['invoice_amount'],
                        'total_amount' => $amounts['total_amount'],
                        'issued_by' => auth()->id(),
                        'issued_at' => now(),
                        'due_date' => $invoice->due_date ?? now()->addDays(7),
                        'last_updated_by' => auth()->id(),
                    ]);

                    // Update reading to BILLED
                    $reading->update(['action_status' => MeterReading::ACTION_STATUS_BILLED]);

                    $issuedCount++;
                } catch (\Exception $e) {
                    $errors[] = "خطأ في فاتورة المشترك {$invoice->subscriber->subscriber_name}: " . $e->getMessage();
                }
            }
        });

        $message = "تم إصدار {$issuedCount} فاتورة بنجاح.";
        if (!empty($errors)) {
            $message .= " مع " . count($errors) . " أخطاء.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'issued_count' => $issuedCount,
            'errors' => $errors,
        ]);
    }

    /**
     * Export invoices to Excel based on current filters
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        // Use same query building as index
        $user = auth()->user();
        $query = Invoice::with(['subscriber', 'meterReading', 'creator']);

        // Apply same role-based filtering as index
        if ($user->isCompanyOwner()) {
            $opIds = $user->ownedOperators()->pluck('id')->toArray();
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $opIds));
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $opIds = $user->operators()->pluck('operators.id')->toArray();
            $query->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $opIds));
        }

        // Apply filters
        if ($request->filled('invoice_status')) {
            $st = $request->input('invoice_status');
            if ($st == '5') {
                // Overdue
                $query->where('invoice_status', Invoice::STATUS_ISSUED)->where('due_date', '<', now());
            } else {
                $query->where('invoice_status', $st);
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('subscriber', fn($q2) => $q2->where('subscription_number', 'like', "%{$search}%")->orWhere('subscriber_name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest('invoice_date')->get();

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('الفواتير');
        $sheet->setRightToLeft(true);

        $headers = ['#', 'رقم الفاتورة', 'رقم الاشتراك', 'اسم المشترك', 'التاريخ', 'الاستهلاك (Kwh)', 'الرصيد السابق', 'مبلغ الفاتورة', 'المبلغ الإجمالي', 'المسدد', 'المتبقي', 'الحالة', 'تاريخ الاستحقاق'];

        $columns = range('A', 'M');
        foreach ($headers as $i => $header) {
            $cell = $columns[$i] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        foreach ($invoices as $idx => $inv) {
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $inv->invoice_number ?? 'مسودة');
            $sheet->setCellValue("C{$row}", $inv->subscriber->subscription_number ?? '');
            $sheet->setCellValue("D{$row}", $inv->subscriber->subscriber_name ?? '');
            $sheet->setCellValue("E{$row}", $inv->invoice_date?->format('Y-m-d'));
            $sheet->setCellValue("F{$row}", $inv->consumption_kwh);
            $sheet->setCellValue("G{$row}", $inv->previous_balance);
            $sheet->setCellValue("H{$row}", $inv->invoice_amount);
            $sheet->setCellValue("I{$row}", $inv->total_amount);
            $sheet->setCellValue("J{$row}", $inv->paidAmount());
            $sheet->setCellValue("K{$row}", $inv->remainingAmount());
            $sheet->setCellValue("L{$row}", $inv->status_name);
            $sheet->setCellValue("M{$row}", $inv->due_date?->format('Y-m-d'));
            $row++;
        }

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'فواتير_' . date('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer->save($tempPath);
        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * جلب بيانات القراءة + المشترك لملء نموذج الفاتورة (AJAX)
     */
    public function getReadingData(Request $request): JsonResponse
    {
        $readingId = (int) $request->input('reading_id', 0);
        $reading   = MeterReading::with('subscriber')->find($readingId);

        if (!$reading) {
            return response()->json(['success' => false, 'message' => 'القراءة غير موجودة.']);
        }

        $subscriber     = $reading->subscriber;
        $activeTariff   = ElectricityTariffPrice::getActivePriceForDate($reading->reading_date);
        $minimumCharge  = (float) Setting::get('invoice_minimum_charge', 0);
        $pricePerKwh    = $activeTariff ? (float) $activeTariff->price_per_kwh : 0;
        $discountRate   = $subscriber->isEligibleForEmployeeDiscount()
            ? Invoice::getEmployeeDiscountRate($reading->reading_date)
            : 0;

        // الرصيد السابق التراكمي: مجموع invoice_amount − مجموع الدفعات
        $previousBalance = Invoice::calculatePreviousBalance($subscriber->id);

        $amounts = Invoice::calculateAmounts(
            (float) $reading->consumption_kwh,
            $pricePerKwh,
            $discountRate,
            $minimumCharge,
            $previousBalance
        );

        return response()->json([
            'success' => true,
            'data' => [
                'subscriber_id'           => $subscriber->id,
                'subscription_number'     => $subscriber->subscription_number,
                'subscriber_name'         => $subscriber->subscriber_name,
                'consumption_kwh'         => (float) $reading->consumption_kwh,
                'consumption_period_days' => $reading->consumption_period_days,
                'reading_date'            => $reading->reading_date->format('Y-m-d'),
                'price_per_kwh'           => $pricePerKwh,
                'discount_rate'           => $discountRate,
                'minimum_charge'          => $minimumCharge,
                'previous_balance'        => round($previousBalance, 2),
                'consumption_cost'        => $amounts['consumption_cost'],  // ثمن الاستهلاك
                'invoice_amount'          => $amounts['invoice_amount'],    // قيمة الفاتورة = max(ثمن, حد أدنى)
                'total_amount'            => $amounts['total_amount'],      // المبلغ المطلوب = max(جمع + رصيد, 0)
            ],
        ]);
    }

    /**
     * جلب مشتركين حسب المشغل (AJAX)
     */
    public function getSubscribersByOperator(Request $request): JsonResponse
    {
        $operatorId = (int) $request->input('operator_id', 0);
        $query = Subscriber::select('id', 'subscription_number', 'subscriber_name');
        if ($operatorId > 0) {
            $query->whereHas('generationUnits', fn($q) => $q->where('operator_id', $operatorId));
        }
        return response()->json([
            'success' => true,
            'subscribers' => $query->orderBy('subscription_number')->limit(200)->get(),
        ]);
    }
}
