<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        $query = Announcement::with(['creator:id,name', 'updater:id,name']);

        // فلتر الحالة: نشط / مخفي / منتهي / كل
        $status = $request->input('status', 'all');
        $today  = now()->toDateString();
        switch ($status) {
            case 'active':
                $query->where('is_visible', true)
                      ->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
                break;
            case 'hidden':
                $query->where('is_visible', false);
                break;
            case 'expired':
                $query->whereDate('end_date', '<', $today);
                break;
            case 'upcoming':
                $query->whereDate('start_date', '>', $today);
                break;
            case 'featured':
                $query->where('is_featured', true);
                break;
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $announcements = $query->orderByDesc('is_featured')
            ->orderByDesc('announcement_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.announcements.index', compact('announcements', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);
        return view('admin.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by']      = Auth::id();
        $data['last_updated_by'] = Auth::id();

        $announcement = Announcement::create($data);

        // إرسال إشعار لكل المستخدمين النشطين عند نشر إعلان ظاهر
        if ($announcement->is_visible) {
            try {
                Notification::notifyAllActiveUsers(
                    'announcement_published',
                    'إعلان جديد: ' . $announcement->title,
                    \Illuminate\Support\Str::limit(strip_tags($announcement->description), 150),
                    route('admin.announcements.show', $announcement)
                );
            } catch (\Throwable $e) {
                \Log::error('Failed to send announcement notifications: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم إنشاء الإعلان بنجاح.');
    }

    public function show(Announcement $announcement): View
    {
        $this->authorize('view', $announcement);
        $announcement->load(['creator:id,name', 'updater:id,name']);
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validated();
        $data['last_updated_by'] = Auth::id();

        $wasVisible = $announcement->is_visible;
        $announcement->update($data);

        // إذا تحوّل من مخفي إلى ظاهر: نرسل إشعار
        if (! $wasVisible && $announcement->is_visible) {
            try {
                Notification::notifyAllActiveUsers(
                    'announcement_published',
                    'إعلان جديد: ' . $announcement->title,
                    \Illuminate\Support\Str::limit(strip_tags($announcement->description), 150),
                    route('admin.announcements.show', $announcement)
                );
            } catch (\Throwable $e) {
                \Log::error('Failed to send announcement notifications: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم تحديث الإعلان بنجاح.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم حذف الإعلان بنجاح.');
    }

    /**
     * تبديل حالة الإظهار/الإخفاء بنقرة واحدة من القائمة.
     */
    public function toggleVisibility(Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $announcement->is_visible = ! $announcement->is_visible;
        $announcement->last_updated_by = Auth::id();
        $announcement->save();

        return back()->with('success', $announcement->is_visible ? 'تم إظهار الإعلان.' : 'تم إخفاء الإعلان.');
    }

    /**
     * تبديل حالة التمييز.
     */
    public function toggleFeatured(Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $announcement->is_featured = ! $announcement->is_featured;
        $announcement->last_updated_by = Auth::id();
        $announcement->save();

        return back()->with('success', $announcement->is_featured ? 'تم تمييز الإعلان.' : 'تم إلغاء التمييز.');
    }
}
