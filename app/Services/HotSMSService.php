<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HotSMSService
{
    private string $baseUrl = 'http://hotsms.ps/sendbulksms.php';
    private string $username;
    private string $password;
    private string $sender;

    public function __construct()
    {
        $this->username = config('services.hotsms.username', 'E-SER-GEDCO');
        $this->password = config('services.hotsms.password', '6770585');
        $this->sender = config('services.hotsms.sender', 'Rased');
    }

    /**
     * إرسال رسالة SMS
     *
     * @param string $mobile رقم الجوال (يجب أن يكون بصيغة 972XXXXXXXXX)
     * @param string $message نص الرسالة
     * @param int $type نوع الرسالة (0: نص عادي, 1: Unicode, 2: UTF-8)
     * @return array ['success' => bool, 'message' => string, 'code' => int]
     */
    public function sendSMS(string $mobile, string $message, int $type = 0): array
    {
        // تحويل رقم الجوال إلى الصيغة المطلوبة (972XXXXXXXXX)
        $mobile = $this->formatMobileNumber($mobile);

        if (!$mobile) {
            return [
                'success' => false,
                'message' => 'رقم الجوال غير صحيح',
                'code' => 5000,
            ];
        }

        try {
            // بناء URL مع المعاملات
            $params = [
                'user_name' => $this->username,
                'user_pass' => $this->password,
                'sender' => $this->sender,
                'mobile' => $mobile,
                'type' => $type,
                'text' => $message,
            ];

            // Log البيانات المرسلة (بدون كلمة المرور)
            Log::info('HotSMS Request', [
                'mobile' => $mobile,
                'sender' => $this->sender,
                'type' => $type,
                'message_length' => mb_strlen($message),
                'username' => $this->username,
            ]);

            $response = Http::timeout(15)->get($this->baseUrl, $params);

            $result = trim($response->body());
            $statusCode = $response->status();

            // Log النتيجة
            Log::info('HotSMS Response', [
                'status_code' => $statusCode,
                'result' => $result,
                'mobile' => $mobile,
            ]);

            // التحقق من رمز النجاح
            if (str_starts_with($result, '1001')) {
                $responseData = [
                    'success' => true,
                    'message' => 'تم إرسال الرسالة بنجاح',
                    'code' => 1001,
                    'message_id' => $this->extractMessageId($result),
                ];
                
                Log::info('HotSMS Success', [
                    'mobile' => $mobile,
                    'message_id' => $responseData['message_id'],
                ]);
                
                return $responseData;
            }

            // معالجة رموز الخطأ
            $errorResponse = $this->handleErrorCode($result);
            
            Log::error('HotSMS Error', [
                'mobile' => $mobile,
                'error_code' => $errorResponse['code'],
                'error_message' => $errorResponse['message'],
                'raw_result' => $result,
            ]);
            
            return $errorResponse;

        } catch (\Exception $e) {
            Log::error('HotSMS API Exception', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage(),
                'code' => 0,
            ];
        }
    }

    /**
     * تحويل رقم الجوال إلى الصيغة المطلوبة (972XXXXXXXXX)
     */
    private function formatMobileNumber(string $mobile): ?string
    {
        // إزالة المسافات والرموز
        $mobile = preg_replace('/[^0-9]/', '', $mobile);

        // إذا بدأ بـ 0، استبدله بـ 972
        if (str_starts_with($mobile, '0')) {
            $mobile = '972' . substr($mobile, 1);
        }
        // إذا بدأ بـ 972، اتركه كما هو
        elseif (!str_starts_with($mobile, '972')) {
            // إذا كان 9 أرقام (بدون 0)، أضف 972
            if (strlen($mobile) == 9) {
                $mobile = '972' . $mobile;
            } else {
                return null; // رقم غير صحيح
            }
        }

        // التحقق من أن الرقم صحيح (972 + 9 أرقام = 12 رقم)
        if (strlen($mobile) !== 12) {
            return null;
        }

        return $mobile;
    }

    /**
     * استخراج Message ID من النتيجة
     */
    private function extractMessageId(string $result): ?string
    {
        if (str_contains($result, '_')) {
            $parts = explode('_', $result);
            return $parts[1] ?? null;
        }
        return null;
    }

    /**
     * معالجة رموز الخطأ
     */
    private function handleErrorCode(string $code): array
    {
        $errorMessages = [
            '1000' => 'لا يوجد رصيد كافي',
            '2000' => 'خطأ في عملية التفويض',
            '3000' => 'خطأ في نوع المسج',
            '4000' => 'أحد المدخلات المطلوبة غير موجود',
            '5000' => 'رقم المحمول غير مدعوم',
            '6000' => 'اسم المرسل غير معرف على حسابك',
            '10000' => 'هذا الأيبي غير مفوض للارسال من خلال هذا الحساب',
            '15000' => 'خاصة الارسال من خلال API غير مفعلة، يرجى تفعيلها من أدوات الأمان لديك',
        ];

        $code = trim($code);
        $message = $errorMessages[$code] ?? 'خطأ غير معروف';

        return [
            'success' => false,
            'message' => $message,
            'code' => (int) $code,
        ];
    }
}
