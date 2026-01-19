<?php

namespace App\Providers;

use App\Models\ComplianceSafety;
use App\Models\FuelEfficiency;
use App\Models\GenerationUnit;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\OperationLog;
use App\Models\Operator;
use App\Models\PermissionAuditLog;
use App\Models\Task;
use App\Models\User;
use App\Policies\ComplianceSafetyPolicy;
use App\Policies\FuelEfficiencyPolicy;
use App\Policies\GenerationUnitPolicy;
use App\Policies\GeneratorPolicy;
use App\Policies\MaintenanceRecordPolicy;
use App\Policies\OperationLogPolicy;
use App\Policies\OperatorPolicy;
use App\Policies\PermissionAuditLogPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Operator::class => OperatorPolicy::class,
        Generator::class => GeneratorPolicy::class,
        GenerationUnit::class => GenerationUnitPolicy::class,
        User::class => UserPolicy::class,
        OperationLog::class => OperationLogPolicy::class,
        FuelEfficiency::class => FuelEfficiencyPolicy::class,
        MaintenanceRecord::class => MaintenanceRecordPolicy::class,
        ComplianceSafety::class => ComplianceSafetyPolicy::class,
        Task::class => TaskPolicy::class,
        \App\Models\AuditLog::class => \App\Policies\AuditLogPolicy::class,
        \App\Models\Message::class => \App\Policies\MessagePolicy::class,
        PermissionAuditLog::class => PermissionAuditLogPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تعيين التوقيت المحلي واللغة
        Carbon::setLocale('ar');
        date_default_timezone_set('Asia/Gaza');

        // استخدام Bootstrap pagination
        Paginator::useBootstrap();

        // Share settings to all views and clean UTF-8 data globally
        View::composer('*', function ($view) {
            try {
                $siteName = \App\Models\Setting::get('site_name', 'راصد');
                // Clean siteName to ensure valid UTF-8
                $siteName = self::cleanStringStatic($siteName);
            } catch (\Exception $e) {
                $siteName = 'راصد';
            }
            $view->with(['siteName' => $siteName]);

            // Clean all session messages and errors to prevent UTF-8 issues
            try {
                // Clean success messages
                if (session()->has('success')) {
                    $success = session('success');
                    if (is_string($success)) {
                        $cleanedSuccess = self::cleanStringStatic($success);
                        if ($cleanedSuccess !== $success) {
                            session()->flash('success', $cleanedSuccess);
                        }
                    }
                }

                // Clean error messages
                if (session()->has('error')) {
                    $error = session('error');
                    if (is_string($error)) {
                        $cleanedError = self::cleanStringStatic($error);
                        if ($cleanedError !== $error) {
                            session()->flash('error', $cleanedError);
                        }
                    }
                }

                // Clean validation errors (only clean, don't recreate if already cleaned)
                $errors = session()->get('errors');
                if ($errors && method_exists($errors, 'all')) {
                    $needsCleaning = false;
                    $cleanedErrors = [];
                    $allErrors = $errors->all();
                    
                    foreach ($allErrors as $key => $message) {
                        if (is_string($message)) {
                            $cleaned = self::cleanStringStatic($message);
                            if ($cleaned !== $message) {
                                $needsCleaning = true;
                            }
                            $cleanedErrors[$key] = $cleaned;
                        } else {
                            $cleanedErrors[$key] = $message;
                        }
                    }
                    
                    if ($needsCleaning && !empty($cleanedErrors)) {
                        session()->flash('errors', new MessageBag($cleanedErrors));
                    }
                }

                // Clean old input values to prevent UTF-8 issues in forms
                $oldInput = session()->getOldInput();
                if (!empty($oldInput)) {
                    $cleanedOld = self::cleanInputArrayStatic($oldInput);
                    // Only update if cleaning changed values
                    if (serialize($cleanedOld) !== serialize($oldInput)) {
                        session()->flashInput($cleanedOld);
                    }
                }
            } catch (\Exception $e) {
                // Silently fail to prevent breaking the view rendering
                \Log::warning('Error cleaning UTF-8 in View Composer', ['error' => $e->getMessage()]);
            }
        });

        // Share settings to front layout
        View::composer('layouts.site', function ($view) {
            try {
                $logoPath = \App\Models\Setting::get('site_logo');
                $logoUrl = $logoPath ? asset($logoPath) : null;
                $siteName = \App\Models\Setting::get('site_name', 'راصد');
                // Clean siteName to ensure valid UTF-8
                $siteName = self::cleanStringStatic($siteName);
            } catch (\Exception $e) {
                $logoUrl = null;
                $siteName = 'راصد';
            }
            $view->with(['logoUrl' => $logoUrl, 'siteName' => $siteName]);
        });
    }

    /**
     * Clean UTF-8 string - removes invalid characters and ensures valid encoding
     * This is a comprehensive solution to prevent Malformed UTF-8 errors globally
     */
    public static function cleanStringStatic(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Convert to string if not already
        $string = (string) $value;

        // Remove BOM if present
        $string = str_replace("\xEF\xBB\xBF", '', $string);

        // Remove invalid UTF-8 sequences using iconv if available
        if (function_exists('iconv')) {
            $string = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
            if ($string === false) {
                $string = (string) $value; // Fallback to original if iconv fails
            }
        }

        // Remove non-printable characters except newlines and tabs
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);

        // Ensure valid UTF-8 encoding
        if (!mb_check_encoding($string, 'UTF-8')) {
            // Try to convert to UTF-8
            $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            
            // If still invalid, use more aggressive cleaning
            if (!mb_check_encoding($string, 'UTF-8')) {
                $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8');
            }
        }

        // Final validation and fallback
        if (!mb_check_encoding($string, 'UTF-8')) {
            // Last resort: remove all non-ASCII characters except Arabic
            $string = preg_replace('/[^\x{0000}-\x{007F}\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', '', $string);
        }

        return trim($string);
    }

    /**
     * Clean UTF-8 string - uses static method for consistency
     */
    private function cleanString(?string $value): string
    {
        return self::cleanStringStatic($value);
    }

    /**
     * Clean array of input values recursively (static version)
     */
    public static function cleanInputArrayStatic(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = self::cleanInputArrayStatic($value);
            } elseif (is_string($value)) {
                $cleaned[$key] = self::cleanStringStatic($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    /**
     * Clean array of input values recursively
     */
    private function cleanInputArray(array $data): array
    {
        return self::cleanInputArrayStatic($data);
    }
}
