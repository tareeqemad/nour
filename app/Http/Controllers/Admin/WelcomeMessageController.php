<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WelcomeMessage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WelcomeMessageController extends Controller
{
    /**
     * عرض قائمة الرسائل الترحيبية
     */
    public function index(): View
    {
        $this->authorize('viewAny', WelcomeMessage::class);

        $messages = WelcomeMessage::orderBy('order')->get();

        return view('admin.welcome-messages.index', compact('messages'));
    }

    /**
     * عرض نموذج تعديل رسالة
     */
    public function edit(WelcomeMessage $welcomeMessage): View
    {
        $this->authorize('update', $welcomeMessage);

        return view('admin.welcome-messages.edit', compact('welcomeMessage'));
    }

    /**
     * تحديث رسالة ترحيبية
     */
    public function update(Request $request, WelcomeMessage $welcomeMessage): RedirectResponse
    {
        $this->authorize('update', $welcomeMessage);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $welcomeMessage->update($validated);

        return redirect()->route('admin.welcome-messages.index')
            ->with('success', 'تم تحديث الرسالة الترحيبية بنجاح.');
    }
}
