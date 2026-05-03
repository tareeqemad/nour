<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MinimumChargeRule;
use App\Models\Operator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinimumChargeRuleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MinimumChargeRule::class);

        $user = auth()->user();
        $operatorId = null;
        $operators = collect();
        $canSelectOperator = $user->hasGlobalAccountingAccess();

        if ($canSelectOperator) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId <= 0 && $operators->isNotEmpty()) {
                $operatorId = $operators->first()->id;
            }
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            $operatorId = $operator?->id;
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operator = $user->operators()->first();
            $operatorId = $operator?->id;
        }

        $rules = $operatorId
            ? MinimumChargeRule::where('operator_id', $operatorId)->orderBy('ampere')->orderBy('phase_type')->get()
            : collect();

        return view('admin.minimum-charge-rules.index', compact('rules', 'operators', 'operatorId', 'canSelectOperator'));
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

        return redirect()->route('admin.minimum-charge-rules.index', ['operator_id' => $minimumChargeRule->operator_id])
            ->with('success', "تم تحديث الحد الأدنى ({$minimumChargeRule->ampere} أمبير / {$minimumChargeRule->phase_name}) إلى {$minimumChargeRule->minimum_charge} ₪ بنجاح.");
    }
}
