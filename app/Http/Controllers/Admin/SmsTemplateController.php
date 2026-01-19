<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SmsTemplateController extends Controller
{
    /**
     * عرض قائمة قوالب SMS
     */
    public function index(): View
    {
        $this->authorize('viewAny', SmsTemplate::class);

        $templates = SmsTemplate::orderBy('name')->get();

        return view('admin.sms-templates.index', compact('templates'));
    }

    /**
     * عرض نموذج تعديل قالب
     */
    public function edit(SmsTemplate $smsTemplate): View
    {
        $this->authorize('update', $smsTemplate);

        return view('admin.sms-templates.edit', compact('smsTemplate'));
    }

    /**
     * تحديث قالب SMS
     */
    public function update(Request $request, SmsTemplate $smsTemplate): RedirectResponse
    {
        $this->authorize('update', $smsTemplate);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template' => ['required', 'string'],
            'max_length' => ['required', 'integer', 'min:100', 'max:160'],
            'is_active' => ['boolean'],
        ]);

        // التحقق من أن القالب لا يتجاوز الحد الأقصى (160 حرف لرسائل SMS)
        if (mb_strlen($validated['template']) > $validated['max_length']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'القالب يتجاوز الحد الأقصى المسموح به (' . $validated['max_length'] . ' حرف).');
        }

        $smsTemplate->update($validated);

        return redirect()->route('admin.sms-templates.index')
            ->with('success', 'تم تحديث قالب SMS بنجاح.');
    }
}
