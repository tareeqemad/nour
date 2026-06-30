<?php

namespace App\Http\Controllers\Madmoun;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * صفحة هبوط منصة مضمون (نموذج بدء).
 * مبرمج مضمون يستبدلها/يوسّعها بحرّية.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('madmoun::dashboard');
    }
}
