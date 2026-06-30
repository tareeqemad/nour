<?php

namespace App\Http\Controllers\Madmoun;

use App\Http\Controllers\Controller;
use App\Models\Madmoun\Policy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * مثال (دور مبرمج مضمون): إدارة بوالص الضمان.
 * شريحة عمودية كاملة: قائمة + إضافة، داخل مساحة مضمون بالكامل.
 */
class PolicyController extends Controller
{
    public function index(): View
    {
        $policies = Policy::latest()->get();

        return view('madmoun::policies.index', compact('policies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'policy_number' => ['required', 'string', 'max:50'],
            'beneficiary'   => ['required', 'string', 'max:120'],
            'amount'        => ['required', 'numeric', 'min:0'],
        ]);

        // اربط البوليصة بمشغّل المستخدم الحالي (من نواة نور).
        $data['operator_id'] = $request->user()->getAffiliatedOperator()?->id;
        $data['status'] = 'active';

        Policy::create($data);

        return redirect()
            ->route('madmoun.policies.index')
            ->with('success', 'تم حفظ البوليصة بنجاح ✅');
    }
}
