<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * عرض الدليل الإرشادي للنظام
     */
    public function index(): View
    {
        return view('admin.guide.index');
    }

    /**
     * دليل التسجيل والمصطلحات: فيديو تعريفي + خطوات + قاموس مصطلحات.
     * رابط الفيديو يُضبط من الإعدادات (intro_video_url).
     */
    public function registration(): View
    {
        $videoUrl = \App\Models\Setting::get('intro_video_url', '');
        $embedUrl = $this->normalizeVideoUrl($videoUrl);

        return view('admin.guide.registration', compact('videoUrl', 'embedUrl'));
    }

    /**
     * تحويل رابط YouTube العادي إلى صيغة embed، وإلا يُعاد الرابط كما هو.
     */
    private function normalizeVideoUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return $url; // mp4 أو رابط embed آخر — يُستخدم كما هو
    }
}



