<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MinimumChargeRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinimumChargeRuleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MinimumChargeRule::class);
        $rules = MinimumChargeRule::orderBy('ampere')->orderBy('phase_type')->get();
        return view('admin.minimum-charge-rules.index', compact('rules'));
    }

    public function update(Request $request, MinimumChargeRule $minimumChargeRule): RedirectResponse
    {
        $this->authorize('update', $minimumChargeRule);
        $validated = $request->validate([
            'minimum_charge' => ['required', 'numeric', 'min:0', 'max:99999.99'],
        ], [
            'minimum_charge.required' => 'قيمة الحد الأدنى مطلوبة.',
            'minimum_charge.numeric'  => 'يجب أن تكون قيمة رقمية.',
            'minimum_charge.min'      => 'يجب أن تكون القيمة 0 أو أكثر.',
        ]);

        $minimumChargeRule->update($validated);

        return redirect()->route('admin.minimum-charge-rules.index')
            ->with('success', "تم تحديث الحد الأدنى ({$minimumChargeRule->ampere} أمبير / {$minimumChargeRule->phase_name}) إلى {$minimumChargeRule->minimum_charge} ₪ بنجاح.");
    }
}
