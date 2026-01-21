<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportAuthorizedPhonesRequest;
use App\Models\AuthorizedPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

class AuthorizedPhoneController extends Controller
{
    /**
     * تنظيف مدخلات البحث لمنع SQL Injection
     * 
     * @param string|null $input
     * @return string
     */
    protected function sanitizeSearchInput(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        // إزالة المسافات الزائدة
        $input = trim($input);
        
        // إزالة HTML tags
        $input = strip_tags($input);
        
        // إزالة الأحرف الخاصة التي قد تستخدم في SQL Injection
        // لكن نترك % و _ لأنها مفيدة في LIKE queries
        $input = preg_replace('/[;\'"\\\]/', '', $input);
        
        // تحديد طول أقصى للبحث (255 حرف)
        $input = mb_substr($input, 0, 255);
        
        return $input;
    }

    /**
     * عرض قائمة الأرقام المصرح بها
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\AuthorizedPhone::class);

        if ($request->wantsJson() || $request->boolean('ajax')) {
            return $this->ajaxIndex($request);
        }

        return view('admin.authorized-phones.index');
    }

    /**
     * AJAX endpoint للجلب
     */
    private function ajaxIndex(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = max(5, min(50, (int) $request->query('per_page', 15)));
        
        // استخدام filled بدلاً من has للتأكد من أن القيمة موجودة وغير فارغة
        $isActive = $request->filled('is_active') ? $request->query('is_active') : null;
        $isRegistered = $request->filled('is_registered') ? $request->query('is_registered') : null;

        $query = AuthorizedPhone::with(['creator', 'updater'])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $search = $this->sanitizeSearchInput($search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            }
        }

        // Filter by status
        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by registration status
        if ($isRegistered !== null && $isRegistered !== '') {
            // البحث عن الأرقام المسجلة من خلال owner في operators
            $registeredPhoneNumbers = \App\Models\User::whereHas('ownedOperators')
                ->whereNotNull('phone')
                ->pluck('phone')
                ->toArray();
            
            if ($isRegistered === '1' || $isRegistered === 1 || $isRegistered === true || $isRegistered === 'true') {
                // مسجل فقط
                $query->whereIn('phone', $registeredPhoneNumbers);
            } else {
                // غير مسجل فقط
                $query->whereNotIn('phone', $registeredPhoneNumbers);
            }
        }

        $phones = $query->paginate($perPage);

        // إضافة معلومات التسجيل لكل رقم - البحث من خلال owner في operators
        $operatorPhonesMap = [];
        $ownersWithPhones = \App\Models\User::whereHas('ownedOperators')
            ->whereNotNull('phone')
            ->with(['ownedOperators' => function($q) {
                $q->with('owner');
            }])
            ->get();
        
        foreach ($ownersWithPhones as $owner) {
            foreach ($owner->ownedOperators as $operator) {
                $operatorPhonesMap[$owner->phone] = $operator;
            }
        }

        $data = $phones->items();
        foreach ($data as $phone) {
            $operator = $operatorPhonesMap[$phone->phone] ?? null;
            $phone->is_registered = $operator !== null;
            $phone->operator = $operator ? [
                'id' => $operator->id,
                'name' => $operator->name,
                'status' => $operator->status,
                'is_approved' => $operator->is_approved,
                'owner_name' => $operator->owner?->name,
            ] : null;
        }

        // إحصائيات
        $totalActive = AuthorizedPhone::where('is_active', true)->count();
        $activePhoneNumbers = AuthorizedPhone::where('is_active', true)->pluck('phone')->toArray();
        $registeredPhones = \App\Models\User::whereHas('ownedOperators')
            ->whereNotNull('phone')
            ->whereIn('phone', $activePhoneNumbers)
            ->count();
        $pendingPhones = $totalActive - $registeredPhones;

        return response()->json([
            'ok' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $phones->currentPage(),
                'last_page' => $phones->lastPage(),
                'from' => $phones->firstItem(),
                'to' => $phones->lastItem(),
                'total' => $phones->total(),
                'per_page' => $phones->perPage(),
            ],
            'stats' => [
                'total_active' => $totalActive,
                'registered' => $registeredPhones,
                'pending' => $pendingPhones,
            ],
        ]);
    }

    /**
     * عرض نموذج إضافة رقم جديد
     */
    public function create(): View
    {
        $this->authorize('create', \App\Models\AuthorizedPhone::class);

        return view('admin.authorized-phones.create');
    }

    /**
     * حفظ رقم جديد
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', \App\Models\AuthorizedPhone::class);

        // تنظيف الرقم أولاً للتحقق من التكرار
        $rawPhone = $request->input('phone', '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

        $validated = $request->validate([
            'phone' => [
                'required', 
                'string', 
                'max:20', 
                'regex:/^0(59|56)\d{7}$/',
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صحيح. يجب أن يبدأ بـ 059 أو 056',
            'name.max' => 'الاسم يجب أن يكون أقل من 255 حرف',
        ]);

        // التحقق من أن الرقم غير مسجل لمستخدم موجود في النظام (بغض النظر عن الدور)
        $existingUser = \App\Models\User::where('phone', $cleanPhone)
            ->orWhere('phone', $rawPhone)
            ->first();
        
        if ($existingUser) {
            $error = 'هذا الرقم مسجل لمستخدم موجود في النظام. لا يمكن إضافته إلى قائمة الأرقام المصرح بها';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->withInput()->withErrors(['phone' => $error]);
        }

        // التحقق من عدم التكرار باستخدام الرقم المنظف (بما في ذلك المحذوفة)
        $existingPhone = AuthorizedPhone::withTrashed()->where('phone', $cleanPhone)->first();
        
        if ($existingPhone) {
            // إذا كان الرقم موجود وغير محذوف
            if (!$existingPhone->trashed()) {
                $error = 'رقم الجوال مسجل مسبقاً';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $error], 422);
                }
                return redirect()->back()->withInput()->withErrors(['phone' => $error]);
            }
            
            // إذا كان الرقم موجود ومحذوف، نستعيده ونحدث بياناته
            $existingPhone->restore();
            $existingPhone->update([
                'name' => $validated['name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            
            $phone = $existingPhone;
        } else {
            // إنشاء رقم جديد
            $phone = AuthorizedPhone::create([
                'phone' => $cleanPhone,
                'name' => $validated['name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => Auth::id(),
            ]);
        }

        // Note: SMS will not be sent automatically
        // Use "Notify Pending" button to send SMS when needed

        $msg = 'تم إضافة الرقم بنجاح ✅';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => $phone,
            ]);
        }

        return redirect()->route('admin.authorized-phones.index')->with('success', $msg);
    }

    /**
     * عرض نموذج تعديل
     */
    public function edit(AuthorizedPhone $authorizedPhone): View
    {
        $this->authorize('update', $authorizedPhone);

        return view('admin.authorized-phones.edit', compact('authorizedPhone'));
    }

    /**
     * تحديث رقم
     */
    public function update(Request $request, AuthorizedPhone $authorizedPhone): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $authorizedPhone);

        $validated = $request->validate([
            'phone' => [
                'required', 
                'string', 
                'max:20', 
                'regex:/^0(59|56)\d{7}$/',
                Rule::unique('authorized_phones', 'phone')
                    ->ignore($authorizedPhone->id)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صحيح. يجب أن يبدأ بـ 059 أو 056',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        // التحقق من أن الرقم غير مسجل لمستخدم موجود في النظام (بغض النظر عن الدور)
        $existingUser = \App\Models\User::where('phone', $cleanPhone)
            ->orWhere('phone', $validated['phone'])
            ->first();
        
        if ($existingUser) {
            $error = 'هذا الرقم مسجل لمستخدم موجود في النظام. لا يمكن إضافته إلى قائمة الأرقام المصرح بها';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->withInput()->withErrors(['phone' => $error]);
        }

        // التحقق من عدم التكرار مرة أخرى (للأمان الإضافي)
        // بما أن الرقم يُحفظ منظفاً دائماً، التحقق من الرقم المنظف
        $existingPhone = AuthorizedPhone::where('id', '!=', $authorizedPhone->id)
            ->where('phone', $cleanPhone)
            ->first();
        
        if ($existingPhone) {
            $error = 'رقم الجوال مسجل مسبقاً';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            return redirect()->back()->withInput()->withErrors(['phone' => $error]);
        }

        $authorizedPhone->update([
            'phone' => $cleanPhone,
            'name' => $validated['name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'updated_by' => Auth::id(),
        ]);

        $msg = 'تم تحديث الرقم بنجاح ✅';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => $authorizedPhone->fresh(),
            ]);
        }

        return redirect()->route('admin.authorized-phones.index')->with('success', $msg);
    }

    /**
     * حذف رقم
     */
    public function destroy(AuthorizedPhone $authorizedPhone): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $authorizedPhone);

        $authorizedPhone->delete();

        $msg = 'تم حذف الرقم بنجاح ✅';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('admin.authorized-phones.index')->with('success', $msg);
    }

    /**
     * تبديل حالة الرقم (تفعيل/إيقاف)
     */
    public function toggleStatus(AuthorizedPhone $authorizedPhone): JsonResponse
    {
        $this->authorize('update', $authorizedPhone);

        $authorizedPhone->is_active = !$authorizedPhone->is_active;
        $authorizedPhone->updated_by = Auth::id();
        $authorizedPhone->save();

        $action = $authorizedPhone->is_active ? 'تفعيل' : 'إيقاف';
        $msg = "تم {$action} الرقم بنجاح ✅";

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => [
                'id' => $authorizedPhone->id,
                'is_active' => $authorizedPhone->is_active,
            ],
        ]);
    }

    /**
     * إرسال إشعار للمشغلين المتبقيين (الذين لم يسجلوا بعد)
     */
    public function notifyPending(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\AuthorizedPhone::class);

        // الحصول على الأرقام المصرح بها المفعلة
        $authorizedPhones = AuthorizedPhone::where('is_active', true)->pluck('phone');
        
        // الحصول على الأرقام المسجلة بالفعل - البحث من خلال owner في operators
        $registeredPhones = \App\Models\User::whereHas('ownedOperators')
            ->whereNotNull('phone')
            ->whereIn('phone', $authorizedPhones)
            ->pluck('phone')
            ->toArray();
        
        // الأرقام المتبقية (غير مسجلة)
        $pendingPhones = $authorizedPhones->diff($registeredPhones);
        
        if ($pendingPhones->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد أرقام متبقية لإرسال إشعار لها',
            ]);
        }

        // إرسال SMS لكل رقم متبقي
        $notifiedCount = 0;
        $failedCount = 0;
        foreach ($pendingPhones as $phone) {
            $authorizedPhone = AuthorizedPhone::where('phone', $phone)->first();
            if ($authorizedPhone) {
                try {
                    $this->sendRegistrationReminderSMS($phone, $authorizedPhone->name);
                    $notifiedCount++;
                } catch (\Exception $e) {
                    \Log::error('Failed to send SMS to pending phone', [
                        'phone' => $phone,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }
            }
        }

        $message = "تم إرسال SMS لـ {$notifiedCount} رقم متبقي";
        if ($failedCount > 0) {
            $message .= " (فشل إرسال {$failedCount} رسالة)";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'notified_count' => $notifiedCount,
            'failed_count' => $failedCount,
        ]);
    }

    /**
     * إرسال SMS ترحيبي عند إضافة رقم جديد
     */
    private function sendWelcomeSMS(string $phone, ?string $name = null): void
    {
        $name = $name ?: 'عزيزي/عزيزتي';
        // Get site name from settings
        $siteName = \App\Models\Setting::get('site_name', 'نور');
        
        // استخدام رابط كامل (full URL) لرسائل SMS
        // url() helper يولد رابط كامل بناءً على APP_URL
        $joinUrl = url('/join');
        
        $message = "مرحباً {$name}،\n\n";
        $message .= "تم إضافة رقمك ({$phone}) إلى قائمة الأرقام المصرح بها في منصة {$siteName}.\n\n";
        $message .= "يمكنك الآن التسجيل في المنصة من خلال الرابط التالي:\n";
        $message .= "{$joinUrl}\n\n";
        $message .= "شكراً لانضمامك إلى منصة {$siteName}.";

        \Log::info('Sending welcome SMS', [
            'phone' => $phone,
            'name' => $name,
            'message_length' => mb_strlen($message),
        ]);

        $smsService = new \App\Services\HotSMSService();
        $result = $smsService->sendSMS($phone, $message, 2);
        
        \Log::info('SMS service response', [
            'phone' => $phone,
            'result' => $result,
        ]);

        // إذا فشل الإرسال، رمي استثناء
        if (!$result['success']) {
            throw new \Exception($result['message'] . ' (كود الخطأ: ' . $result['code'] . ')');
        }
    }

    /**
     * إرسال SMS تذكيري للمشغلين المتبقيين
     */
    private function sendRegistrationReminderSMS(string $phone, ?string $name = null): void
    {
        $name = $name ?: 'عزيزي/عزيزتي';
        // Get site name from settings
        $siteName = \App\Models\Setting::get('site_name', 'نور');
        
        // استخدام رابط كامل (full URL) لرسائل SMS
        // url() helper يولد رابط كامل بناءً على APP_URL
        $joinUrl = url('/join');
        
        $message = "مرحباً {$name}،\n\n";
        $message .= "نود تذكيرك بأن رقمك ({$phone}) مصرح به في منصة {$siteName} ولكن لم يتم التسجيل بعد.\n\n";
        $message .= "نرجو منك إكمال التسجيل في المنصة من خلال الرابط التالي:\n";
        $message .= "{$joinUrl}\n\n";
        $message .= "شكراً لانضمامك إلى منصة {$siteName}.";

        $smsService = new \App\Services\HotSMSService();
        $smsService->sendSMS($phone, $message, 2);
    }

    /**
     * حذف جميع الأرقام المصرح بها
     */
    public function deleteAll(): JsonResponse
    {
        $this->authorize('deleteAll', \App\Models\AuthorizedPhone::class);

        $count = AuthorizedPhone::count();
        
        if ($count === 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد أرقام للحذف',
            ]);
        }

        // حذف جميع الأرقام
        AuthorizedPhone::query()->delete();

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$count} رقم بنجاح ✅",
            'deleted_count' => $count,
        ]);
    }

    /**
     * Import authorized phones from Excel file
     */
    public function import(ImportAuthorizedPhonesRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', \App\Models\AuthorizedPhone::class);

        // Increase execution time limit for large imports
        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', '300');

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();

            // Load Excel file
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            if ($highestRow < 2) {
                $error = 'الملف فارغ أو لا يحتوي على بيانات للاستيراد';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $error], 422);
                }
                return redirect()->back()->with('error', $error);
            }

            // Find column indices by header (case-insensitive, flexible)
            $headerRow = 1;
            $phoneColumn = null;
            $nameColumn = null;
            $notesColumn = null;

            // Search for columns in first row (if headers exist)
            $hasHeaders = false;
            foreach ($sheet->getRowIterator($headerRow, $headerRow) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $value = trim(strtolower((string) $cell->getValue()));
                    $column = $cell->getColumn();

                    // Check if this row looks like headers (contains Arabic/English text related to our columns)
                    if (!empty($value) && (
                        str_contains($value, 'اسم') || str_contains($value, 'name') ||
                        str_contains($value, 'جوال') || str_contains($value, 'phone') ||
                        str_contains($value, 'ملاحظ') || str_contains($value, 'note')
                    )) {
                        $hasHeaders = true;
                    }

                    // Match name column (exact match or contains) - should be first
                    if (in_array($value, ['الاسم', 'name', 'اسم', 'المالك', 'صاحب المولد', 'المشغل']) 
                        || str_contains($value, 'اسم') || str_contains($value, 'name')) {
                        $nameColumn = $column;
                    }
                    // Match phone column (exact match or contains) - should be second
                    elseif (in_array($value, ['رقم الجوال', 'الهاتف', 'phone', 'mobile', 'رقم الهاتف', 'جوال', 'رقم', 'الجوال', 'تلفون']) 
                        || str_contains($value, 'جوال') || str_contains($value, 'هاتف') || str_contains($value, 'phone') || str_contains($value, 'mobile')) {
                        $phoneColumn = $column;
                    }
                    // Match notes column (exact match or contains) - optional third column
                    elseif (in_array($value, ['ملاحظات', 'notes', 'ملاحظة', 'الملاحظات', 'تفاصيل', 'details']) 
                        || str_contains($value, 'ملاحظ') || str_contains($value, 'note')) {
                        $notesColumn = $column;
                    }
                }
            }

            // Default order: A=name, B=phone, C=notes (if no headers found or columns not identified)
            if (!$hasHeaders || !$nameColumn || !$phoneColumn) {
                $nameColumn = 'A';  // العمود الأول: الاسم
                $phoneColumn = 'B'; // العمود الثاني: رقم الجوال
                $notesColumn = $notesColumn ?: 'C'; // العمود الثالث: الملاحظات (اختياري)
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Load all existing phones once (to avoid N+1 queries)
            // Group by phone number for quick lookup, include deleted_at for soft delete check
            $existingPhones = AuthorizedPhone::withTrashed()
                ->get(['id', 'phone', 'deleted_at'])
                ->keyBy('phone')
                ->map(function ($phone) {
                    return [
                        'id' => $phone->id,
                        'deleted_at' => $phone->deleted_at,
                    ];
                })
                ->toArray();

            // Load all existing users with their phones and roles (for validation)
            // Group by phone for quick lookup
            $existingUsers = \App\Models\User::whereNotNull('phone')
                ->get(['id', 'phone', 'role', 'role_id'])
                ->keyBy('phone')
                ->toArray();

            // Determine start row: skip header row if headers exist, otherwise start from row 1
            $startRow = $hasHeaders && ($nameColumn || $phoneColumn) ? 2 : 1;

            // Process rows starting from $startRow
            DB::beginTransaction();

            try {
                $phonesToInsert = [];
                $phonesToUpdate = [];
                // Track phones already processed in this import batch to avoid duplicates within the file
                $processedPhones = [];

                for ($row = $startRow; $row <= $highestRow; $row++) {
                    // Read and clean UTF-8 data from Excel cells
                    $phoneValue = $this->cleanCellValue($sheet->getCell($phoneColumn . $row)->getValue());
                    $nameValue = $nameColumn ? $this->cleanCellValue($sheet->getCell($nameColumn . $row)->getValue()) : '';
                    $notesValue = $notesColumn ? $this->cleanCellValue($sheet->getCell($notesColumn . $row)->getValue()) : '';

                    // Skip empty rows
                    if (empty($phoneValue)) {
                        continue;
                    }

                    // Clean phone number (remove all non-numeric characters)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phoneValue);

                    // If phone doesn't start with 0, add it
                    // Handle cases like: 599562127 -> 0599562127, or 569876543 -> 0569876543
                    if (!str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 9) {
                        // If it's a 9-digit number (missing leading 0), add 0 at the beginning
                        $cleanPhone = '0' . $cleanPhone;
                    }

                    // Validate phone number format (should start with 059 or 056 and be 10 digits)
                    if (!preg_match('/^0(59|56)\d{7}$/', $cleanPhone)) {
                        // Phone value is already cleaned by cleanCellValue
                        $errors[] = "السطر {$row}: رقم الجوال '{$phoneValue}' غير صحيح";
                        $skipped++;
                        continue;
                    }

                    // Check if phone was already processed in this import batch (duplicate within file)
                    if (isset($processedPhones[$cleanPhone])) {
                        $errors[] = "السطر {$row}: رقم الجوال '{$cleanPhone}' مكرر في نفس الملف (تم معالجته مسبقاً في السطر {$processedPhones[$cleanPhone]})";
                        $skipped++;
                        continue;
                    }

                    // Mark this phone as processed
                    $processedPhones[$cleanPhone] = $row;

                    // التحقق من أن الرقم غير مسجل لمستخدم موجود في النظام (بغض النظر عن الدور)
                    $existingUser = $existingUsers[$cleanPhone] ?? null;
                    if ($existingUser) {
                        $errors[] = "السطر {$row}: رقم الجوال '{$cleanPhone}' مسجل لمستخدم موجود في النظام. لا يمكن إضافته";
                        $skipped++;
                        continue;
                    }

                    // Check if phone already exists in database (using in-memory lookup)
                    $existingPhone = $existingPhones[$cleanPhone] ?? null;

                    if ($existingPhone) {
                        if (empty($existingPhone['deleted_at'])) {
                            // Phone exists and is active, skip
                            $skipped++;
                            continue;
                        } else {
                            // Phone was soft deleted, prepare to restore and update
                            // Values are already cleaned by cleanCellValue
                            $phonesToUpdate[] = [
                                'id' => $existingPhone['id'],
                                'name' => $nameValue ?: null,
                                'notes' => $notesValue ?: null,
                            ];
                            $imported++;
                        }
                    } else {
                        // Prepare for batch insert
                        // Values are already cleaned by cleanCellValue
                        $phonesToInsert[] = [
                            'phone' => $cleanPhone,
                            'name' => $nameValue ?: null,
                            'notes' => $notesValue ?: null,
                            'is_active' => true,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $imported++;
                    }
                }

                // Batch insert new phones (much faster than individual inserts)
                if (!empty($phonesToInsert)) {
                    // Remove duplicates from phonesToInsert array (extra safety check)
                    $uniquePhonesToInsert = [];
                    $seenPhones = [];
                    foreach ($phonesToInsert as $phoneData) {
                        $phone = $phoneData['phone'];
                        if (!isset($seenPhones[$phone])) {
                            $seenPhones[$phone] = true;
                            $uniquePhonesToInsert[] = $phoneData;
                        } else {
                            // This should not happen, but log it for debugging
                            \Log::warning('Duplicate phone found in phonesToInsert array before insert', [
                                'phone' => $phone,
                            ]);
                            $skipped++;
                            $imported--; // Decrease imported count as we're skipping a duplicate
                        }
                    }
                    
                    // Insert in chunks of 500 to avoid memory issues
                    foreach (array_chunk($uniquePhonesToInsert, 500) as $chunk) {
                        AuthorizedPhone::insert($chunk);
                    }
                }

                // Restore and update soft-deleted phones (batch process)
                if (!empty($phonesToUpdate)) {
                    // Remove duplicates from phonesToUpdate (in case same phone ID appears multiple times)
                    $uniquePhonesToUpdate = [];
                    $seenIds = [];
                    foreach ($phonesToUpdate as $phoneData) {
                        $id = $phoneData['id'];
                        if (!isset($seenIds[$id])) {
                            $seenIds[$id] = true;
                            $uniquePhonesToUpdate[] = $phoneData;
                        } else {
                            // This should not happen, but log it for debugging
                            \Log::warning('Duplicate phone ID found in phonesToUpdate array', [
                                'id' => $id,
                            ]);
                        }
                    }
                    
                    $phoneIds = array_column($uniquePhonesToUpdate, 'id');
                    $phonesMap = array_combine(
                        $phoneIds,
                        $uniquePhonesToUpdate
                    );
                    
                    // Restore all soft-deleted phones at once by updating deleted_at
                    AuthorizedPhone::withTrashed()
                        ->whereIn('id', $phoneIds)
                        ->whereNotNull('deleted_at')
                        ->update([
                            'deleted_at' => null,
                            'updated_at' => now(),
                        ]);
                    
                    // Update each phone
                    foreach ($phonesMap as $id => $phoneData) {
                        AuthorizedPhone::where('id', $id)->update([
                            'name' => $phoneData['name'],
                            'notes' => $phoneData['notes'],
                            'is_active' => true,
                            'updated_by' => Auth::id(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::commit();

                // Note: SMS will not be sent during import to avoid timeout
                // Users can use "Notify Pending" button to send SMS later if needed

                $message = "تم استيراد {$imported} رقم بنجاح";
                if ($skipped > 0) {
                    $message .= " (تم تخطي {$skipped} رقم)";
                }
                if (!empty($errors)) {
                    // Clean errors array to ensure valid UTF-8
                    $cleanErrors = array_map(function($error) {
                        return mb_convert_encoding($error, 'UTF-8', 'UTF-8');
                    }, array_slice($errors, 0, 10));
                    
                    $message .= ". الأخطاء: " . implode('; ', $cleanErrors);
                    if (count($errors) > 10) {
                        $message .= ' و' . (count($errors) - 10) . ' خطأ آخر';
                    }
                }
                $message .= ". ملاحظة: لم يتم إرسال رسائل SMS تلقائياً أثناء الاستيراد لتسريع العملية. يمكنك استخدام زر 'إشعار المتبقيين' لإرسال SMS لاحقاً.";

                if ($request->wantsJson() || $request->ajax()) {
                    // Ensure JSON encoding works correctly with UTF-8
                    $response = [
                        'success' => true,
                        'message' => mb_convert_encoding($message, 'UTF-8', 'UTF-8'),
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'errors' => !empty($errors) ? array_map(function($error) {
                            return mb_convert_encoding($error, 'UTF-8', 'UTF-8');
                        }, array_slice($errors, 0, 20)) : [],
                    ];
                    
                    return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                }

                return redirect()->route('admin.authorized-phones.index')
                    ->with('success', $message)
                    ->with('import_errors', $errors);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ReaderException $e) {
            $errorMessage = $e->getMessage();
            // Clean error message to ensure valid UTF-8
            $errorMessage = $this->cleanCellValue($errorMessage);
            $error = 'حدث خطأ أثناء قراءة ملف Excel: ' . $errorMessage;
            
            Log::error('Excel import error', ['error' => $errorMessage, 'trace' => $e->getTraceAsString()]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => mb_convert_encoding($error, 'UTF-8', 'UTF-8')
                ], 422, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }

            return redirect()->back()->with('error', $error);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            // Clean error message to ensure valid UTF-8
            $errorMessage = $this->cleanCellValue($errorMessage);
            $error = 'حدث خطأ أثناء استيراد البيانات: ' . $errorMessage;
            
            Log::error('Import error', ['error' => $errorMessage, 'trace' => $e->getTraceAsString()]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => mb_convert_encoding($error, 'UTF-8', 'UTF-8')
                ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }

            return redirect()->back()->with('error', $error);
        }
    }

    /**
     * Clean UTF-8 string from Excel cell value
     * Handles malformed UTF-8 characters and converts them properly
     */
    private function cleanCellValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Convert to string
        $string = (string) $value;

        // Remove BOM if present
        $string = str_replace("\xEF\xBB\xBF", '', $string);

        // Remove null bytes and other problematic characters
        $string = str_replace(["\x00", "\r"], '', $string);

        // Try to convert to UTF-8 if not already valid
        if (!mb_check_encoding($string, 'UTF-8')) {
            $detectedEncoding = @mb_detect_encoding($string, ['UTF-8', 'Windows-1256', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
            if ($detectedEncoding && $detectedEncoding !== 'UTF-8') {
                $converted = @mb_convert_encoding($string, 'UTF-8', $detectedEncoding);
                if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                    $string = $converted;
                }
            }
        }

        // Remove invalid UTF-8 sequences using iconv
        if (function_exists('iconv') && !mb_check_encoding($string, 'UTF-8')) {
            $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE//TRANSLIT', $string);
            if ($cleaned !== false) {
                $string = $cleaned;
            } else {
                // If iconv fails, use mb_convert_encoding as fallback
                $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            }
        }

        // Remove non-printable characters (except spaces, newlines, tabs)
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);

        // Final validation and cleanup
        if (!mb_check_encoding($string, 'UTF-8')) {
            // Last resort: remove invalid UTF-8 sequences
            $string = preg_replace('/[\x{FFFD}]/u', '', $string);
            $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        }

        return trim($string);
    }
}
