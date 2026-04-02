<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Helpers\UsernameHelper;
use App\Models\AuthorizedPhone;
use App\Models\Operator;
use App\Models\ProcessedPortalRequest;
use App\Models\User;
use App\Services\HotSMSService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessApprovedPortalRequests extends Command
{
    protected $signature = 'portal:process-approved
                            {--app-no= : معالجة طلب محدد برقم الطلب}
                            {--dry-run : عرض النتائج بدون إنشاء حسابات}
                            {--processed-by= : معرّف المستخدم الذي طلب المعالجة (يدوي)}';

    protected $description = 'إنشاء حسابات مستخدمين تلقائياً من الطلبات المنفذة على البوابة الرقمية';

    private string $baseUrl;
    private string $token;

    public function handle(): int
    {
        $this->token   = config('services.portal.token', '');
        $this->baseUrl = rtrim(config('services.portal.base_url', 'https://e.services.gov.ps/api/ministry/'), '/') . '/';

        if (empty($this->token)) {
            $this->error('Portal API token not configured.');
            Log::error('portal:process-approved - TOKEN not configured');
            return self::FAILURE;
        }

        $specificAppNo = $this->option('app-no');
        $dryRun        = $this->option('dry-run');
        $processedBy   = $this->option('processed-by') ? (int) $this->option('processed-by') : null;

        if ($specificAppNo) {
            return $this->processSpecificApp($specificAppNo, $dryRun, $processedBy);
        }

        return $this->processAllApproved($dryRun, $processedBy);
    }

    private function processSpecificApp(string $appNo, bool $dryRun, ?int $processedBy): int
    {
        $this->info("جاري معالجة الطلب رقم: {$appNo}");

        // جلب بيانات الطلب من API
        $appData = $this->fetchApp($appNo);
        if (!$appData) {
            $this->error("تعذر جلب بيانات الطلب {$appNo}");
            return self::FAILURE;
        }

        $result = $this->processOneApp($appNo, $appData, $dryRun, $processedBy);

        if ($result === 'created') {
            $this->info("تم إنشاء حساب بنجاح للطلب {$appNo}");
            return self::SUCCESS;
        } elseif ($result === 'skipped') {
            $this->warn("تم تخطي الطلب {$appNo} (مسجل مسبقاً أو بيانات ناقصة)");
            return self::SUCCESS;
        } else {
            $this->error("فشل معالجة الطلب {$appNo}");
            return self::FAILURE;
        }
    }

    private function processAllApproved(bool $dryRun, ?int $processedBy): int
    {
        $this->info('جاري جلب الطلبات المنفذة بنجاح (حالة 100)...');

        $page     = 1;
        $perPage  = 50;
        $created  = 0;
        $skipped  = 0;
        $failed   = 0;

        do {
            $response = $this->fetchApprovedRequests($page, $perPage);
            if (!$response) {
                $this->error('تعذر جلب الطلبات من البوابة');
                break;
            }

            $items    = $response['items'];
            $allPages = $response['allPages'];

            if (empty($items)) {
                $this->info('لا توجد طلبات منفذة.');
                break;
            }

            foreach ($items as $item) {
                $appNo = $item['app_no'] ?? '';
                if (empty($appNo)) continue;

                // تخطي الطلبات المعالجة مسبقاً
                if (ProcessedPortalRequest::isProcessed($appNo)) {
                    $skipped++;
                    continue;
                }

                // جلب بيانات الطلب التفصيلية (pureData)
                $appData = $this->fetchApp($appNo);
                if (!$appData) {
                    $this->warn("تعذر جلب تفاصيل الطلب {$appNo}");
                    $failed++;
                    continue;
                }

                $result = $this->processOneApp($appNo, $appData, $dryRun, $processedBy);

                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'skipped') {
                    $skipped++;
                } else {
                    $failed++;
                }

                // تأخير بسيط لتجنب إغراق الـ API
                usleep(300000); // 300ms
            }

            $this->info("الصفحة {$page}/{$allPages} - تم إنشاء: {$created}, تخطي: {$skipped}, فشل: {$failed}");
            $page++;

        } while ($page <= $allPages);

        $this->info("اكتمل. إنشاء: {$created} | تخطي: {$skipped} | فشل: {$failed}");
        Log::info('portal:process-approved completed', compact('created', 'skipped', 'failed'));

        return self::SUCCESS;
    }

    private function processOneApp(string $appNo, array $appData, bool $dryRun, ?int $processedBy): string
    {
        $pureData   = $appData['pureData'] ?? $appData['pure_data'] ?? [];
        $statusInfo = $appData['status'] ?? [];
        $statusCode = $statusInfo['status'] ?? null;

        // التحقق من أن الحالة هي 100 (تم التنفيذ)
        if ((int)$statusCode !== 100) {
            Log::info("portal:process-approved - App {$appNo} status is {$statusCode}, not 100. Skipping.");
            $this->recordProcessed($appNo, null, null, null, 'skipped', 'الحالة ليست 100 (تم التنفيذ)', $processedBy);
            return 'skipped';
        }

        // استخراج البيانات من pureData
        $name = $this->extractFromPureData($pureData, [
            'الاسم كاملاً',
            'الاسم الكامل',
            'الاسم رباعي',
            'اسم مقدم الطلب',
            'الاسم',
        ]);

        $idNumber = $this->extractFromPureData($pureData, [
            'رقم الهوية',
            'رقم الهويه',
            'رقم هوية مقدم الطلب',
        ]);

        $phone = $this->extractFromPureData($pureData, [
            'رقم الجوال',
            'رقم الموبايل',
            'رقم الهاتف',
            'الجوال',
            'الموبايل',
            'هاتف مقدم الطلب',
        ]);

        $operatorName = $this->extractFromPureData($pureData, [
            'اسم المشغل',
            'اسم محطة التوليد',
            'اسم المحطة',
            'اسم الجهة المشغلة',
        ]);

        // اذا لم نجد اسم مشغل، نستخدم اسم مقدم الطلب
        if (empty($operatorName)) {
            $operatorName = $name;
        }

        // التحقق من البيانات الأساسية
        if (empty($name) || empty($idNumber)) {
            $msg = 'بيانات ناقصة - الاسم: ' . ($name ?: 'مفقود') . ', الهوية: ' . ($idNumber ?: 'مفقود');
            Log::warning("portal:process-approved - App {$appNo}: {$msg}");
            $this->recordProcessed($appNo, $idNumber, $name, $phone, 'skipped', $msg, $processedBy);
            return 'skipped';
        }

        // تنظيف البيانات
        $cleanName      = $this->cleanString($name);
        $cleanIdNumber  = preg_replace('/[^0-9]/', '', $idNumber);
        $cleanPhone     = $phone ? preg_replace('/[^0-9]/', '', $phone) : null;
        $cleanOperator  = $this->cleanString($operatorName);

        if ($dryRun) {
            $this->info("  [DRY-RUN] الطلب {$appNo}: {$cleanName} | هوية: {$cleanIdNumber} | جوال: {$cleanPhone}");
            return 'created';
        }

        // التحقق من عدم وجود مستخدم بنفس رقم الهوية
        $existingOperator = Operator::where('owner_id_number', $cleanIdNumber)->first();
        if ($existingOperator) {
            $msg = "رقم الهوية {$cleanIdNumber} مسجل مسبقاً (مشغل #{$existingOperator->id})";
            Log::info("portal:process-approved - App {$appNo}: {$msg}");
            $this->recordProcessed($appNo, $cleanIdNumber, $cleanName, $cleanPhone, 'skipped', $msg, $processedBy);
            return 'skipped';
        }

        // التحقق من الجوال (إن وُجد)
        if ($cleanPhone) {
            $existingUserByPhone = User::where('phone', $cleanPhone)->first();
            if ($existingUserByPhone) {
                $existingOp = Operator::where('owner_id', $existingUserByPhone->id)->first();
                if ($existingOp) {
                    $msg = "الجوال {$cleanPhone} مسجل مسبقاً (مستخدم #{$existingUserByPhone->id})";
                    Log::info("portal:process-approved - App {$appNo}: {$msg}");
                    $this->recordProcessed($appNo, $cleanIdNumber, $cleanName, $cleanPhone, 'skipped', $msg, $processedBy);
                    return 'skipped';
                }
            }
        }

        // إنشاء الحساب
        try {
            return DB::transaction(function () use ($appNo, $cleanName, $cleanIdNumber, $cleanPhone, $cleanOperator, $processedBy) {
                // إضافة الجوال للأرقام المصرح بها (إذا لم يكن مضافاً)
                if ($cleanPhone && !AuthorizedPhone::isAuthorized($cleanPhone)) {
                    AuthorizedPhone::create([
                        'phone'     => $cleanPhone,
                        'name'      => $cleanName,
                        'notes'     => 'تمت الإضافة تلقائياً من طلب البوابة رقم ' . $appNo,
                        'is_active' => true,
                    ]);
                }

                // توليد username - نمرر رقم الهوية كاسم لضمان username واضح
                // لأن البوابة لا توفر اسم إنجليزي، نستخدم رقم الهوية مباشرة
                $username = UsernameHelper::generate(Role::CompanyOwner, $cleanIdNumber, $cleanIdNumber);

                // توليد email
                $email   = $username . '@gazarased.com';
                $counter = 1;
                while (User::where('email', $email)->whereNull('deleted_at')->exists()) {
                    $email = $username . '_' . $counter . '@gazarased.com';
                    $counter++;
                }

                // توليد password
                $password = Str::random(8);
                $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
                if (strlen($password) < 8) {
                    $password = str_pad($password, 8, Str::random(1), STR_PAD_RIGHT);
                }
                $passwordPlain = $password;

                // إنشاء User
                $user = User::create([
                    'name'     => $cleanName,
                    'name_en'  => $cleanName, // الاسم من البوابة عربي - سيُحدّث لاحقاً
                    'email'    => $email,
                    'username' => $username,
                    'password' => Hash::make($password),
                    'phone'    => $cleanPhone ?: null,
                    'role'     => Role::CompanyOwner,
                    'status'   => 'active',
                ]);

                // إنشاء Operator
                $operator = Operator::create([
                    'name'               => $cleanOperator,
                    'owner_id'           => $user->id,
                    'owner_name'         => $cleanName,
                    'owner_id_number'    => $cleanIdNumber,
                    'phone'              => $cleanPhone ?: null,
                    'status'             => 'active',
                    'is_approved'        => false,
                    'profile_completed'  => false,
                ]);

                // إرسال SMS ببيانات الدخول
                if ($cleanPhone) {
                    try {
                        $this->sendCredentialsSMS($cleanPhone, $username, $passwordPlain);
                    } catch (\Exception $e) {
                        Log::error("portal:process-approved - SMS failed for app {$appNo}", [
                            'phone' => $cleanPhone,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // إنشاء رسائل الترحيب
                try {
                    $this->createWelcomeMessages($user, $operator, $username);
                } catch (\Exception $e) {
                    Log::error("portal:process-approved - Welcome messages failed for app {$appNo}", [
                        'error' => $e->getMessage(),
                    ]);
                }

                // تسجيل المعالجة
                $this->recordProcessed($appNo, $cleanIdNumber, $cleanName, $cleanPhone, 'success',
                    "تم إنشاء حساب: {$username} (مستخدم #{$user->id}, مشغل #{$operator->id})", $processedBy);

                Log::info("portal:process-approved - Created account for app {$appNo}", [
                    'user_id'     => $user->id,
                    'operator_id' => $operator->id,
                    'username'    => $username,
                ]);

                $this->info("  تم إنشاء حساب [{$username}] للطلب {$appNo}");

                return 'created';
            });
        } catch (\Exception $e) {
            Log::error("portal:process-approved - Failed to create account for app {$appNo}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->recordProcessed($appNo, $cleanIdNumber, $cleanName, $cleanPhone, 'failed', $e->getMessage(), $processedBy);
            $this->error("  فشل إنشاء حساب للطلب {$appNo}: {$e->getMessage()}");
            return 'failed';
        }
    }

    // ── API Methods ──────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'TOKEN'  => $this->token,
            'Accept' => 'application/json',
        ];
    }

    private function fetchApprovedRequests(int $page, int $perPage): ?array
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($this->baseUrl . 'getAppsByStatus', [
                    'status'       => 100,
                    'page'         => $page,
                    'countPerPage' => $perPage,
                ]);

            if (!$res->successful()) {
                Log::error('portal:process-approved - API error: ' . $res->status());
                return null;
            }

            $body = $res->json();
            if (!($body['status'] ?? false)) {
                return null;
            }

            $apps       = $body['apps'] ?? [];
            $items      = $apps['data'] ?? [];
            $pagination = $apps['pagination'] ?? [];

            return [
                'items'    => $items,
                'allPages' => (int)($pagination['allPages'] ?? 1),
            ];
        } catch (\Exception $e) {
            Log::error('portal:process-approved - fetchApprovedRequests: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchApp(string $appNo): ?array
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(30)
                ->get($this->baseUrl . 'getApp/' . urlencode($appNo));

            if (!$res->successful()) {
                return null;
            }

            $body = $res->json();
            if (!($body['status'] ?? false)) {
                return null;
            }

            return $body['0'] ?? $body['app'] ?? null;
        } catch (\Exception $e) {
            Log::error("portal:process-approved - fetchApp({$appNo}): " . $e->getMessage());
            return null;
        }
    }

    // ── Data Extraction ──────────────────────────────────────────

    private function extractFromPureData(mixed $pureData, array $fieldNames): ?string
    {
        if (empty($pureData)) {
            return null;
        }

        if (is_string($pureData)) {
            $pureData = json_decode($pureData, true);
        }

        if (!is_array($pureData)) {
            return null;
        }

        foreach ($pureData as $section) {
            $dtData = $section['dt_data'] ?? [];
            foreach ($dtData as $row) {
                if (!is_array($row)) continue;
                foreach ($row as $field) {
                    if (!is_array($field)) continue;
                    $fn = $field['fn'] ?? '';
                    if (in_array($fn, $fieldNames, true)) {
                        $fv = $field['fv'] ?? [];
                        if (is_array($fv) && !empty($fv)) {
                            return $fv[0];
                        }
                    }
                }
            }
        }

        return null;
    }

    // ── SMS & Messages ───────────────────────────────────────────

    private function sendCredentialsSMS(string $phone, string $username, string $password): void
    {
        $loginUrl = url('/login');
        $siteName = \App\Models\Setting::get('site_name', 'نور');

        $message = "{$siteName}\n";
        $message .= "المستخدم: {$username}\n";
        $message .= "كلمة المرور: {$password}\n";
        $message .= "رابط: {$loginUrl}";

        if (mb_strlen($message) > 70) {
            $message = "{$siteName}\n";
            $message .= "المستخدم: {$username}\n";
            $message .= "كلمة المرور: {$password}\n";
            $parsedUrl = parse_url($loginUrl);
            $shortUrl  = ($parsedUrl['host'] ?? '') . ($parsedUrl['path'] ?? '/login');
            $message  .= $shortUrl;
        }

        $smsService = new HotSMSService();
        $result     = $smsService->sendSMS($phone, $message, 2);

        if ($result['success']) {
            Log::info("portal:process-approved - SMS sent to {$phone}");
        } else {
            Log::error("portal:process-approved - SMS failed to {$phone}: {$result['message']}");
        }
    }

    private function createWelcomeMessages(User $user, Operator $operator, string $username): void
    {
        $systemUser = User::where('username', 'platform_rased')->first();
        if (!$systemUser) {
            Log::warning('portal:process-approved - System user (platform_rased) not found');
            return;
        }

        $existingCount = \App\Models\Message::where('receiver_id', $user->id)
            ->where('operator_id', $operator->id)
            ->where('type', 'admin_to_operator')
            ->count();

        if ($existingCount >= 3) {
            return;
        }

        $loginUrl           = route('login');
        $changePasswordUrl  = route('admin.profile.show') . '#change-password';
        $siteName           = \App\Models\Setting::get('site_name', 'نور');

        $messages = [
            [
                'title'   => 'مرحباً بك في منصة ' . $siteName,
                'content' => "مرحباً بك في منصة {$siteName} لإدارة الطاقة.\n\n"
                    . "تم إنشاء حسابك بنجاح.\n"
                    . "اسم المستخدم: {$username}\n\n"
                    . "رابط الدخول: {$loginUrl}\n\n"
                    . "نتمنى لك تجربة مميزة!",
            ],
            [
                'title'   => 'تغيير كلمة المرور',
                'content' => "ننصحك بتغيير كلمة المرور الافتراضية لحماية حسابك.\n\n"
                    . "يمكنك تغييرها من: {$changePasswordUrl}",
            ],
            [
                'title'   => 'إكمال الملف الشخصي',
                'content' => "يرجى إكمال ملف المشغل الخاص بك بإضافة:\n"
                    . "- بيانات وحدات التوليد\n"
                    . "- بيانات المولدات\n"
                    . "- بيانات المشتركين\n\n"
                    . "سيتم مراجعة بياناتك من قبل الإدارة للاعتماد.",
            ],
        ];

        foreach ($messages as $msg) {
            try {
                \App\Models\Message::create([
                    'sender_id'   => $systemUser->id,
                    'receiver_id' => $user->id,
                    'operator_id' => $operator->id,
                    'title'       => $msg['title'],
                    'content'     => $msg['content'],
                    'type'        => 'admin_to_operator',
                    'is_read'     => false,
                ]);
            } catch (\Exception $e) {
                Log::error('portal:process-approved - Failed to create welcome message', [
                    'title' => $msg['title'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function recordProcessed(
        string $appNo,
        ?string $applicantId,
        ?string $name,
        ?string $phone,
        string $status,
        ?string $notes,
        ?int $processedBy
    ): void {
        ProcessedPortalRequest::updateOrCreate(
            ['app_no' => $appNo],
            [
                'applicant_id'   => $applicantId,
                'applicant_name' => $name,
                'phone'          => $phone,
                'status'         => $status,
                'notes'          => $notes,
                'processed_at'   => now(),
                'processed_by'   => $processedBy,
            ]
        );
    }

    private function cleanString(?string $value): ?string
    {
        if ($value === null) return null;
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        $value = preg_replace('/\xC2[\x80-\x9F]/u', '', $value);
        return trim($value);
    }
}
