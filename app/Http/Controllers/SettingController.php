<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const BUSINESS_KEYS = [
        'business_name',
        'gstin',
        'address',
        'country',
        'business_country',
        'state',
        'business_state',
        'currency',
        'currency_symbol',
        'logo',
        'business_logo',
        'favicon',
        'email',
        'phone',
        'terms_conditions',
    ];

    private const INVOICE_KEYS = [
        'company_prefix',
        'quote_prefix',
        'order_prefix',
        'invoice_prefix',
        'default_due_days',
        'due_days',
        'default_gst_rate',
    ];

    private const EMAIL_KEYS = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
    ];

    private const PAYMENT_KEYS = [
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
    ];

    public function __construct(private readonly SettingService $service)
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $keys = array_values(array_unique(array_merge(
            self::BUSINESS_KEYS,
            self::INVOICE_KEYS,
            self::EMAIL_KEYS,
            self::PAYMENT_KEYS,
        )));

        $settings = array_merge([
            'business_name' => 'Invoice Pro',
            'gstin' => '',
            'address' => '',
            'country' => 'India',
            'business_country' => 'India',
            'state' => '',
            'business_state' => '',
            'currency' => 'INR',
            'currency_symbol' => 'Rs',
            'logo' => null,
            'business_logo' => null,
            'favicon' => null,
            'email' => '',
            'phone' => '',
            'terms_conditions' => '',
            'company_prefix' => '',
            'quote_prefix' => 'QT',
            'order_prefix' => 'ORD',
            'invoice_prefix' => 'INV',
            'default_due_days' => 15,
            'due_days' => 15,
            'default_gst_rate' => 18,
            'mail_mailer' => 'smtp',
            'mail_host' => '',
            'mail_port' => 587,
            'mail_username' => '',
            'mail_password' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',
            'bank_name' => '',
            'account_number' => '',
            'ifsc_code' => '',
            'upi_id' => '',
        ], $this->service->getMany($keys));

        $states = $this->getIndianStates();
        $selectedStateCode = (string) ($settings['business_state'] ?? '');

        if ($selectedStateCode === '' && ! empty($settings['state'])) {
            $resolvedCode = array_search((string) $settings['state'], $states, true);
            $selectedStateCode = $resolvedCode !== false ? (string) $resolvedCode : '';
        }

        if ($selectedStateCode !== '' && array_key_exists($selectedStateCode, $states)) {
            $settings['business_state'] = $selectedStateCode;
            $settings['state'] = $states[$selectedStateCode];
        } else {
            $settings['state'] = '';
            $settings['business_state'] = '';
        }

        $settings['country'] = 'India';
        $settings['business_country'] = 'India';
        $settings['currency'] = 'INR';
        $settings['currency_symbol'] = 'Rs';
        $settings['mail_password'] = '';

        return view('settings.index', [
            'settings' => $settings,
            'states' => $states,
            'logoUrl' => setting_media_url('logo', 'business_logo'),
            'faviconUrl' => setting_media_url('favicon'),
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1024'],
            'business_country' => ['required', 'string', 'in:India'],
            'business_state' => ['nullable', 'string', 'in:MH,DL,GJ,KA,TN,RJ,UP,MP,WB,PB'],
            'logo' => ['nullable', 'image', 'max:3072'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'terms_conditions' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('branding/logos', 'public');
            $this->replaceFile('logo', $logoPath);
            $this->service->set('business_logo', $logoPath);
        }

        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('branding/favicons', 'public');
            $this->replaceFile('favicon', $faviconPath);
        }

        $states = $this->getIndianStates();
        $stateCode = $this->nullIfEmpty($validated['business_state'] ?? null);
        $stateName = $stateCode !== null ? ($states[$stateCode] ?? null) : null;

        $this->persistSettings([
            'business_name' => trim((string) $validated['business_name']),
            'gstin' => $this->nullIfEmpty($validated['gstin'] ?? null),
            'address' => $this->nullIfEmpty($validated['address'] ?? null),
            'country' => 'India',
            'business_country' => 'India',
            'state' => $stateName,
            'business_state' => $stateCode,
            'currency' => 'INR',
            'currency_symbol' => 'Rs',
            'email' => $this->nullIfEmpty($validated['email'] ?? null),
            'phone' => $this->nullIfEmpty($validated['phone'] ?? null),
            'terms_conditions' => $this->nullIfEmpty($validated['terms_conditions'] ?? null),
        ]);

        return to_route('settings.index')->with('status', 'Business profile saved successfully.');
    }

    public function updateInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_prefix' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9]*$/'],
            'quote_prefix' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'order_prefix' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'invoice_prefix' => ['required', 'string', 'max:50'],
            'default_due_days' => ['required', 'integer', 'min:0', 'max:365'],
            'default_gst_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $dueDays = (int) $validated['default_due_days'];

        $this->persistSettings([
            'company_prefix' => $this->sanitizePrefix($validated['company_prefix'] ?? null),
            'quote_prefix' => $this->sanitizePrefix($validated['quote_prefix'] ?? null, 'QT'),
            'order_prefix' => $this->sanitizePrefix($validated['order_prefix'] ?? null, 'ORD'),
            'invoice_prefix' => $this->sanitizePrefix($validated['invoice_prefix'] ?? null, 'INV'),
            'default_due_days' => $dueDays,
            'due_days' => $dueDays,
            'default_gst_rate' => (float) $validated['default_gst_rate'],
        ]);

        return to_route('settings.index')->with('status', 'Invoice settings saved successfully.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'string', 'in:smtp,mailgun,ses,postmark,log'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'send_test_email' => ['nullable', 'boolean'],
            'test_email_recipient' => ['nullable', 'email', 'max:255'],
        ]);

        $payload = [
            'mail_mailer' => trim((string) $validated['mail_mailer']),
            'mail_host' => $this->nullIfEmpty($validated['mail_host'] ?? null),
            'mail_port' => isset($validated['mail_port']) ? (int) $validated['mail_port'] : null,
            'mail_username' => $this->nullIfEmpty($validated['mail_username'] ?? null),
            'mail_from_address' => $this->nullIfEmpty($validated['mail_from_address'] ?? null),
            'mail_from_name' => $this->nullIfEmpty($validated['mail_from_name'] ?? null),
        ];

        if (($validated['mail_password'] ?? '') !== '') {
            $payload['mail_password'] = (string) $validated['mail_password'];
        }

        $this->persistSettings($payload);
        apply_user_mail_config((int) Auth::id());

        if ($request->boolean('send_test_email')) {
            $recipient = $validated['test_email_recipient'] ?: Auth::user()?->email;

            if (! $recipient) {
                return to_route('settings.index')
                    ->withErrors(['test_email_recipient' => 'Please enter a valid recipient for the test email.']);
            }

            try {
                Mail::mailer(setting('mail_mailer', 'smtp'))
                    ->to($recipient)
                    ->send(new class extends Mailable {
                        public function build(): self
                        {
                            $fromAddress = setting('mail_from_address');
                            $fromName = setting('mail_from_name');

                            if ($fromAddress) {
                                $this->from($fromAddress, $fromName ?: null);
                            }

                            return $this
                                ->subject('Settings Test Email')
                                ->html('<p>Your GST invoicing app mail settings are working.</p>');
                        }
                    });
            } catch (\Throwable $exception) {
                return to_route('settings.index')
                    ->withErrors(['test_email_recipient' => 'Unable to send test email: '.$exception->getMessage()]);
            }

            return to_route('settings.index')->with('status', 'Email settings saved and test email sent.');
        }

        return to_route('settings.index')->with('status', 'Email settings saved successfully.');
    }

    public function updatePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:64'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'upi_id' => ['nullable', 'string', 'max:64'],
        ]);

        $this->persistSettings([
            'bank_name' => $this->nullIfEmpty($validated['bank_name'] ?? null),
            'account_number' => $this->nullIfEmpty($validated['account_number'] ?? null),
            'ifsc_code' => $this->nullIfEmpty($validated['ifsc_code'] ?? null),
            'upi_id' => $this->nullIfEmpty($validated['upi_id'] ?? null),
        ]);

        return to_route('settings.index')->with('status', 'Payment settings saved successfully.');
    }

    private function persistSettings(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->service->set((string) $key, $value);
        }
    }

    private function replaceFile(string $key, string $newPath): void
    {
        $oldPath = normalize_storage_path((string) setting($key, ''));

        $this->service->set($key, $newPath);

        if ($oldPath && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    private function nullIfEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function sanitizePrefix(?string $value, ?string $fallback = null): ?string
    {
        if ($value === null) {
            return $fallback !== null ? strtoupper($fallback) : null;
        }

        $normalized = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', trim($value)));

        if ($normalized !== '') {
            return $normalized;
        }

        return $fallback !== null ? strtoupper($fallback) : null;
    }

    private function getIndianStates(): array
    {
        return [
            'MH' => 'Maharashtra',
            'DL' => 'Delhi',
            'GJ' => 'Gujarat',
            'KA' => 'Karnataka',
            'TN' => 'Tamil Nadu',
            'RJ' => 'Rajasthan',
            'UP' => 'Uttar Pradesh',
            'MP' => 'Madhya Pradesh',
            'WB' => 'West Bengal',
            'PB' => 'Punjab',
        ];
    }
}
