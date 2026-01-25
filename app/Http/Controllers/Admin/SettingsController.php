<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ChecksSuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use ChecksSuperAdmin;

    public function index(): View
    {
        $this->checkSuperAdmin();
        
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'settings' => 'required|array',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'favicon' => 'nullable|mimes:ico|max:512',
        ], [
            'favicon.mimes' => 'يجب أن يكون ملف Favicon بامتداد .ico فقط',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'nour_logo.' . $logo->getClientOriginalExtension();
            
            // Copy to public directory
            $publicPath = public_path('assets/admin/images/brand-logos');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            $logo->move($publicPath, $logoName);
            
            Setting::set('site_logo', 'assets/admin/images/brand-logos/' . $logoName, 'image', 'logo', 'لوجو الموقع', 'لوجو الموقع الرئيسي');
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            // Force .ico extension
            $faviconName = 'favicon.ico';
            
            // Copy to public directory
            $publicPath = public_path('assets/admin/images/brand-logos');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            // Delete old favicon if exists
            $oldFavicon = $publicPath . '/favicon.ico';
            if (file_exists($oldFavicon)) {
                unlink($oldFavicon);
            }
            $favicon->move($publicPath, $faviconName);
            
            Setting::set('site_favicon', 'assets/admin/images/brand-logos/' . $faviconName, 'image', 'logo', 'أيقونة الموقع', 'أيقونة الموقع (Favicon)');
        }

        // Update other settings
        $settings = $request->input('settings', []);
        
        // Handle primary_color - prefer hex value if provided
        if (isset($settings['primary_color_hex']) && !empty($settings['primary_color_hex'])) {
            $settings['primary_color'] = $settings['primary_color_hex'];
        }
        unset($settings['primary_color_hex']); // Remove hex key to avoid duplicate
        
        // Handle dark_color - prefer hex value if provided
        if (isset($settings['dark_color_hex']) && !empty($settings['dark_color_hex'])) {
            $settings['dark_color'] = $settings['dark_color_hex'];
        }
        unset($settings['dark_color_hex']); // Remove hex key to avoid duplicate
        
        // Handle header_color - prefer hex value if provided
        if (isset($settings['header_color_hex']) && !empty($settings['header_color_hex'])) {
            $settings['header_color'] = $settings['header_color_hex'];
        }
        unset($settings['header_color_hex']); // Remove hex key to avoid duplicate
        
        // Track if any color was changed to clear cache
        $colorChanged = false;
        
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            // Check if color or style was changed (before update/create)
            if (in_array($key, ['primary_color', 'dark_color', 'header_color', 'menu_styles', 'header_styles'])) {
                if ($setting && $setting->value !== $value) {
                    $colorChanged = true;
                } elseif (!$setting) {
                    $colorChanged = true;
                }
            }
            
            if ($setting) {
                $setting->update(['value' => $value]);
            } else {
                $group = 'general';
                $type = 'text';
                
                // Set appropriate group and type for specific settings
                if ($key === 'primary_color' || $key === 'dark_color' || $key === 'header_color') {
                    $group = 'design';
                    $type = 'color';
                } elseif ($key === 'menu_styles' || $key === 'header_styles') {
                    $group = 'design';
                    $type = 'select';
                }
                
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => $type,
                    'group' => $group,
                ]);
            }
        }

        // Clear cache if any color was changed
        if ($colorChanged) {
            Cache::flush();
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم تحديث الإعدادات بنجاح.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'nullable',
            'type' => 'required|in:text,textarea,number,image,email,url',
            'group' => 'required|string',
            'label' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Setting::create($request->only(['key', 'value', 'type', 'group', 'label', 'description']));

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم إضافة الإعداد بنجاح.');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $this->checkSuperAdmin();
        
        // Don't allow deletion of critical settings
        $criticalSettings = ['site_logo', 'site_favicon', 'site_name'];
        if (in_array($setting->key, $criticalSettings)) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'لا يمكن حذف هذا الإعداد لأنه إعداد أساسي.');
        }

        $setting->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم حذف الإعداد بنجاح.');
    }
}
