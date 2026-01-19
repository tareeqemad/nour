<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * عرض صفحة الـ logs
     */
    public function index(Request $request): View|JsonResponse
    {
        // التحقق من الصلاحية
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('logs.view')) {
            abort(403, 'لا تملك صلاحية للوصول إلى هذه الصفحة');
        }

        if ($request->wantsJson() || $request->boolean('ajax')) {
            return $this->ajaxIndex($request);
        }

        return view('admin.logs.index');
    }

    /**
     * AJAX endpoint للجلب
     */
    private function ajaxIndex(Request $request): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ]);
        }

        $search = trim((string) $request->query('search', ''));
        $level = $request->query('level', ''); // error, warning, info, etc.
        $perPage = max(10, min(100, (int) $request->query('per_page', 50)));
        $page = max(1, (int) $request->query('page', 1));

        // قراءة ملف الـ log (قراءة من النهاية للأحدث)
        $content = File::get($logFile);
        $lines = explode("\n", $content);
        
        // تحليل الـ logs
        $logs = [];
        $currentLog = null;
        $inTrace = false;
        
        foreach ($lines as $line) {
            // التحقق من بداية سجل جديد (يبدأ بـ [YYYY-MM-DD HH:MM:SS])
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s*(.+)$/', $line, $matches)) {
                // حفظ السجل السابق إذا كان موجوداً
                if ($currentLog) {
                    $logs[] = $currentLog;
                }
                
                // بدء سجل جديد
                $currentLog = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => strtoupper($matches[3]),
                    'message' => $matches[4],
                    'context' => '',
                    'trace' => '',
                ];
                $inTrace = false;
            } elseif ($currentLog) {
                // إضافة السطر إلى context أو trace
                if (str_contains($line, 'Stack trace:') || str_contains($line, '#0') || str_contains($line, '#1') || str_contains($line, '#2')) {
                    $inTrace = true;
                    $currentLog['trace'] .= $line . "\n";
                } elseif ($inTrace) {
                    // إذا كنا في trace، نستمر في إضافة السطور
                    if (trim($line) !== '' && (str_starts_with(trim($line), '#') || str_contains($line, '.php'))) {
                        $currentLog['trace'] .= $line . "\n";
                    } else {
                        $inTrace = false;
                        if (trim($line) !== '') {
                            $currentLog['context'] .= $line . "\n";
                        }
                    }
                } else {
                    // إضافة إلى context
                    if (trim($line) !== '') {
                        $currentLog['context'] .= $line . "\n";
                    }
                }
            }
        }
        
        // إضافة آخر سجل
        if ($currentLog) {
            $logs[] = $currentLog;
        }

        // عكس الترتيب (الأحدث أولاً)
        $logs = array_reverse($logs);

        // فلترة حسب البحث
        if ($search !== '') {
            $logs = array_filter($logs, function ($log) use ($search) {
                return str_contains(strtolower($log['message']), strtolower($search)) ||
                       str_contains(strtolower($log['context']), strtolower($search)) ||
                       str_contains(strtolower($log['trace']), strtolower($search));
            });
        }

        // فلترة حسب المستوى
        if ($level !== '') {
            $logs = array_filter($logs, function ($log) use ($level) {
                return strtolower($log['level']) === strtolower($level);
            });
        }

        // إعادة ترقيم المصفوفة بعد الفلترة
        $logs = array_values($logs);

        // Pagination
        $total = count($logs);
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = array_slice($logs, $offset, $perPage);

        return response()->json([
            'ok' => true,
            'data' => $paginatedLogs,
            'meta' => [
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
                'total' => $total,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * حذف ملف الـ log
     */
    public function clear(): JsonResponse
    {
        // التحقق من الصلاحية
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('logs.clear')) {
            abort(403, 'لا تملك صلاحية لحذف الـ logs');
        }

        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف ملف الـ logs بنجاح ✅',
        ]);
    }

    /**
     * تحميل ملف الـ log
     */
    public function download()
    {
        // التحقق من الصلاحية
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('logs.download')) {
            abort(403, 'لا تملك صلاحية لتحميل الـ logs');
        }

        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            abort(404, 'ملف الـ log غير موجود');
        }

        return response()->download($logFile, 'laravel-' . date('Y-m-d') . '.log');
    }
}
