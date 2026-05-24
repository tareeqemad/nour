<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * إدارة النسخ الاحتياطية لقاعدة البيانات — SuperAdmin فقط.
 */
class BackupController extends Controller
{
    public function __construct(private BackupService $service) {}

    /**
     * فحص أن المستخدم سوبر أدمن. يستدعى في بداية كل action.
     */
    private function ensureSuperAdmin(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'هذه الصفحة متاحة فقط لمدير النظام.');
    }

    public function index(): View
    {
        $this->ensureSuperAdmin();

        $backups = $this->service->list();

        return view('admin.backups.index', compact('backups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        try {
            $path = $this->service->create();
            $name = basename($path);
            return redirect()->route('admin.backups.index')
                ->with('success', "تم إنشاء النسخة الاحتياطية بنجاح: {$name}");
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage());
        }
    }

    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $this->ensureSuperAdmin();

        $path = $this->service->resolvePath($filename);
        if (! $path) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'الملف غير موجود أو الاسم غير صالح.');
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->ensureSuperAdmin();

        if ($this->service->delete($filename)) {
            return redirect()->route('admin.backups.index')
                ->with('success', "تم حذف النسخة الاحتياطية: {$filename}");
        }

        return redirect()->route('admin.backups.index')
            ->with('error', 'تعذّر حذف الملف — قد يكون محذوفاً أو الاسم غير صالح.');
    }
}
