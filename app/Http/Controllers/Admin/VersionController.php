<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VersionLog;
use App\Traits\ChecksSuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VersionController extends Controller
{
    use ChecksSuperAdmin;
    /**
     * صفحة حول النظام
     */
    public function about(): View
    {
        $currentVersion = VersionLog::getCurrentVersion();
        $versionNumber = $currentVersion?->version ?? Setting::get('app_version', '1.0.0');
        
        // معلومات النظام
        $systemInfo = [
            'version' => $versionNumber,
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];
        
        return view('admin.version.about', compact('currentVersion', 'systemInfo'));
    }

    /**
     * صفحة سجل التغييرات
     */
    public function changelog(): View
    {
        $versions = VersionLog::latest()->paginate(10);
        
        return view('admin.version.changelog', compact('versions'));
    }

    /**
     * صفحة إدارة الإصدارات (للسوبر أدمن فقط)
     */
    public function index(): View
    {
        $this->checkSuperAdmin();
        
        $versions = VersionLog::latest()->paginate(15);
        $currentVersion = VersionLog::getCurrentVersion();
        
        return view('admin.version.index', compact('versions', 'currentVersion'));
    }

    /**
     * صفحة إنشاء إصدار جديد
     */
    public function create(): View
    {
        $this->checkSuperAdmin();
        
        $nextVersions = [
            'major' => VersionLog::generateNextVersion('major'),
            'minor' => VersionLog::generateNextVersion('minor'),
            'patch' => VersionLog::generateNextVersion('patch'),
        ];
        
        return view('admin.version.create', compact('nextVersions'));
    }

    /**
     * حفظ إصدار جديد
     */
    public function store(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $validated = $this->validateVersion($request);
        
        $version = VersionLog::create([
            'version' => $validated['version'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'changes' => $this->collectChanges($request),
            'type' => $validated['type'],
            'release_date' => $validated['release_date'],
            'is_current' => false,
            'released_by' => auth()->id(),
        ]);
        
        // تعيين كإصدار حالي إذا تم طلب ذلك
        if ($request->boolean('is_current')) {
            $this->setVersionAsCurrent($version);
        }
        
        return redirect()->route('admin.versions.index')
            ->with('success', 'تم إنشاء الإصدار بنجاح');
    }

    /**
     * عرض تفاصيل إصدار
     */
    public function show(VersionLog $version): View
    {
        return view('admin.version.show', compact('version'));
    }

    /**
     * صفحة تعديل إصدار
     */
    public function edit(VersionLog $version): View
    {
        $this->checkSuperAdmin();
        
        return view('admin.version.edit', compact('version'));
    }

    /**
     * تحديث إصدار
     */
    public function update(Request $request, VersionLog $version): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $validated = $this->validateVersion($request, $version->id);
        
        $version->update([
            'version' => $validated['version'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'changes' => $this->collectChanges($request),
            'type' => $validated['type'],
            'release_date' => $validated['release_date'],
        ]);
        
        // تعيين كإصدار حالي إذا تم طلب ذلك
        if ($request->boolean('is_current')) {
            $this->setVersionAsCurrent($version);
        }
        
        return redirect()->route('admin.versions.index')
            ->with('success', 'تم تحديث الإصدار بنجاح');
    }

    /**
     * حذف إصدار
     */
    public function destroy(VersionLog $version): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        if ($version->is_current) {
            return redirect()->route('admin.versions.index')
                ->with('error', 'لا يمكن حذف الإصدار الحالي');
        }
        
        $version->delete();
        
        return redirect()->route('admin.versions.index')
            ->with('success', 'تم حذف الإصدار بنجاح');
    }

    /**
     * تعيين إصدار كإصدار حالي
     */
    public function setCurrent(VersionLog $version): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $this->setVersionAsCurrent($version);
        
        return redirect()->route('admin.versions.index')
            ->with('success', 'تم تعيين الإصدار ' . $version->version . ' كإصدار حالي');
    }

    /**
     * التحقق من بيانات الإصدار
     * @param Request $request
     * @param int|null $ignoreId معرف الإصدار المراد استثناؤه (للتعديل)
     * @return array
     */
    private function validateVersion(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = $ignoreId 
            ? 'unique:version_logs,version,' . $ignoreId 
            : 'unique:version_logs,version';

        return $request->validate([
            'version' => 'required|string|max:20|' . $uniqueRule,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:major,minor,patch',
            'release_date' => 'required|date',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:500',
            'fixes' => 'nullable|array',
            'fixes.*' => 'nullable|string|max:500',
            'improvements' => 'nullable|array',
            'improvements.*' => 'nullable|string|max:500',
            'security' => 'nullable|array',
            'security.*' => 'nullable|string|max:500',
            'is_current' => 'nullable|boolean',
        ], [
            'version.required' => 'رقم الإصدار مطلوب',
            'version.unique' => 'رقم الإصدار موجود مسبقاً',
            'title.required' => 'عنوان الإصدار مطلوب',
            'type.required' => 'نوع الإصدار مطلوب',
            'release_date.required' => 'تاريخ الإصدار مطلوب',
        ]);
    }

    /**
     * تجميع التغييرات من الطلب
     * @param Request $request
     * @return array
     */
    private function collectChanges(Request $request): array
    {
        return [
            'features' => array_values(array_filter($request->input('features', []))),
            'fixes' => array_values(array_filter($request->input('fixes', []))),
            'improvements' => array_values(array_filter($request->input('improvements', []))),
            'security' => array_values(array_filter($request->input('security', []))),
        ];
    }

    /**
     * تعيين إصدار كحالي وتحديث الإعدادات
     * @param VersionLog $version
     */
    private function setVersionAsCurrent(VersionLog $version): void
    {
        $version->setAsCurrent();
        Setting::set('app_version', $version->version, 'text', 'system', 'إصدار التطبيق');
    }
}
