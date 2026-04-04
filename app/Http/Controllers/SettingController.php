<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected SettingService $service)
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $defaults = [
            'business_name' => config('invoice.business_name', 'Your Business Name'),
            'gstin' => config('company.gstin', ''),
            'address' => config('company.address', ''),
            'state' => config('invoice.state', 'Karnataka'),
            'logo' => null,
            'invoice_prefix' => config('invoice.invoice_prefix', 'INV'),
            'default_due_days' => config('invoice.default_due_days', 15),
            'default_gst_rate' => 18,
            'currency' => config('invoice.currency', 'INR'),
            'currency_symbol' => config('invoice.currency_symbol', '₹'),
            'email_from_name' => config('mail.from.name', env('MAIL_FROM_NAME', 'Laravel')),
            'email_from_address' => config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
            'email_signature' => '',
            'enable_ai_calls' => true,
            'ai_reminder_delay' => 3,
            'ai_max_follow_up_attempts' => 3,
            'ai_call_tone' => 'formal',
            'ai_language' => 'English',
            'upi_id' => '',
            'bank_name' => '',
            'account_number' => '',
            'ifsc_code' => '',
        ];

        $settings = array_merge($defaults, $this->service->getMany(array_keys($defaults)));
        $logoUrl = null;

        if (! empty($settings['logo']) && Storage::disk('public')->exists($settings['logo'])) {
            $logoUrl = asset('storage/' . $settings['logo']);
        }

        return view('settings.index', [
            'settings' => $settings,
            'logoUrl' => $logoUrl,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['nullable', 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:1024'],
            'state' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'invoice_prefix' => ['nullable', 'string', 'max:50'],
            'default_due_days' => ['required', 'integer', 'min:0', 'max:365'],
            'default_gst_rate' => ['nullable', 'numeric', 'between:0,100'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'email_from_address' => ['nullable', 'email', 'max:255'],
            'email_signature' => ['nullable', 'string', 'max:1024'],
            'enable_ai_calls' => ['nullable', 'boolean'],
            'ai_reminder_delay' => ['required', 'integer', 'min:0', 'max:90'],
            'ai_max_follow_up_attempts' => ['required', 'integer', 'min:0', 'max:20'],
            'ai_call_tone' => ['required', 'string', 'in:formal,friendly'],
            'ai_language' => ['required', 'string', 'in:English,Hindi,Hinglish'],
            'upi_id' => ['nullable', 'string', 'max:64'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:64'],
            'ifsc_code' => ['nullable', 'string', 'max:15'],
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $this->service->set('logo', $logoPath);
        }

        $fields = [
            'business_name' => $validated['business_name'] ?? null,
            'gstin' => $validated['gstin'] ?? null,
            'address' => $validated['address'] ?? null,
            'state' => $validated['state'] ?? null,
            'invoice_prefix' => $validated['invoice_prefix'] ?? null,
            'default_due_days' => (int) $validated['default_due_days'],
            'default_gst_rate' => isset($validated['default_gst_rate']) ? (float) $validated['default_gst_rate'] : null,
            'currency' => $validated['currency'] ?? 'INR',
            'currency_symbol' => $validated['currency_symbol'] ?? '₹',
            'email_from_name' => $validated['email_from_name'] ?? null,
            'email_from_address' => $validated['email_from_address'] ?? null,
            'email_signature' => $validated['email_signature'] ?? null,
            'enable_ai_calls' => $request->boolean('enable_ai_calls'),
            'ai_reminder_delay' => (int) $validated['ai_reminder_delay'],
            'ai_max_follow_up_attempts' => (int) $validated['ai_max_follow_up_attempts'],
            'ai_call_tone' => $validated['ai_call_tone'],
            'ai_language' => $validated['ai_language'],
            'upi_id' => $validated['upi_id'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'ifsc_code' => $validated['ifsc_code'] ?? null,
        ];

        foreach ($fields as $key => $value) {
            $this->service->set($key, $value);
        }

        $this->service->forgetCache();

        return redirect()->route('settings.index')->with('status', 'Settings updated successfully.');
    }
}
