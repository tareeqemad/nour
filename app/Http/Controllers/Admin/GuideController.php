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
}



