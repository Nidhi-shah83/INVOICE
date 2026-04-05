<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailable;
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
            // Business
            'business_name' => SettingService::defaultFor('business_name') ?? config('invoice.business_name', 'Invoice Pro'),
            'gstin' => SettingService::defaultFor('gstin') ?? config('company.gstin', '-'),
            'address' => config('company.address', ''),
            'country' => 'India',
            'state' => '',
            'business_logo' => null,
            'favicon' => null,
            'email' => SettingService::defaultFor('email') ?? config('invoice.email', ''),
            'phone' => SettingService::defaultFor('phone') ?? '',
            'terms_conditions' => SettingService::defaultFor('terms_conditions') ?? '',

            // Invoice
            'invoice_prefix' => config('invoice.invoice_prefix', 'INV'),
            'default_due_days' => (int) (SettingService::defaultFor('default_due_days') ?? config('invoice.default_due_days', 15)),
            'due_days' => (int) (SettingService::defaultFor('due_days') ?? config('invoice.default_due_days', 15)),
            'default_gst_rate' => 18,
            'currency' => 'INR',
            'currency_symbol' => '₹',

            // Email
            'mail_mailer' => config('mail.default', 'smtp'),
            'mail_scheme' => config('mail.mailers.smtp.scheme'),
            'mail_host' => config('mail.mailers.smtp.host', ''),
            'mail_port' => config('mail.mailers.smtp.port', 587),
            'mail_username' => config('mail.mailers.smtp.username', ''),
            'mail_password' => config('mail.mailers.smtp.password', ''),
            'mail_from_address' => config('mail.from.address', ''),
            'mail_from_name' => config('mail.from.name', ''),

            // Payment
            'upi_id' => '',
            'bank_name' => '',
            'account_number' => '',
            'ifsc_code' => '',
        ];

        $settings = array_merge($defaults, $this->service->getMany(array_keys($defaults)));
        $displayLogo = $settings['business_logo'] ?: ($settings['logo'] ?? null);
        $logoUrl = null;
        $faviconUrl = null;

        $logoUrl = $this->storageImageDataUri($displayLogo);
        $faviconUrl = $this->storageImageDataUri($settings['favicon'] ?? null);

        // Calculate completion progress
        $progress = $this->calculateProgress($settings);

        return view('settings.index', [
            'settings' => $settings,
            'logoUrl' => $logoUrl,
            'faviconUrl' => $faviconUrl,
            'progress' => $progress,
            'locations' => config('locations.countries', []),
            'states' => get_states('India'),
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'gstin' => ['required', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:1024'],
            'state' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image'],
            'favicon' => ['nullable', 'image'],
            'email' => ['nullable', 'email', 'max:255'],
            'terms_conditions' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $this->service->set('business_logo', $logoPath);
            $this->service->set('logo', $logoPath);
        }

        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            $this->service->set('favicon', $faviconPath);
        }

        $fields = [
            'business_name' => $validated['business_name'] ?? null,
            'gstin' => $validated['gstin'] ?? null,
            'address' => $validated['address'] ?? null,
            'country' => 'India',
            'state' => $validated['state'] ?? null,
            'email' => $validated['email'] ?? null,
            'terms_conditions' => $validated['terms_conditions'] ?? null,
        ];

        foreach ($fields as $key => $value) {
            $this->service->set($key, $value);
        }

        $this->service->forgetCache();

        return redirect()->route('settings.index')->with('status', 'Business settings updated successfully.');
    }

    public function updateInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_prefix' => ['nullable', 'string', 'max:50'],
            'default_due_days' => ['required', 'integer', 'min:0', 'max:365'],
            'default_gst_rate' => ['nullable', 'numeric', 'between:0,100'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:5'],
        ]);

        $fields = [
            'invoice_prefix' => $validated['invoice_prefix'] ?? null,
            'default_due_days' => (int) $validated['default_due_days'],
            'due_days' => (int) $validated['default_due_days'],
            'default_gst_rate' => isset($validated['default_gst_rate']) ? (float) $validated['default_gst_rate'] : null,
            'currency' => $validated['currency'] ?? 'INR',
            'currency_symbol' => $validated['currency_symbol'] ?? '₹',
        ];

        foreach ($fields as $key => $value) {
            $this->service->set($key, $value);
        }

        $this->service->forgetCache();

        return redirect()->route('settings.index')->with('status', 'Invoice settings updated successfully.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'string', 'in:smtp,mailgun,ses,postmark'],
            'mail_scheme' => ['nullable', 'string', 'max:20'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'test_email_recipient' => ['nullable', 'email', 'max:255'],
        ]);

        $mailScheme = isset($validated['mail_scheme']) ? strtolower(trim((string) $validated['mail_scheme'])) : null;
        if ($mailScheme === '' || $mailScheme === 'null') {
            $mailScheme = null;
        }

        $fields = [
            'mail_mailer' => $validated['mail_mailer'],
            'mail_scheme' => $mailScheme,
            'mail_host' => $validated['mail_host'] ?? null,
            'mail_port' => isset($validated['mail_port']) ? (int) $validated['mail_port'] : null,
            'mail_username' => $validated['mail_username'] ?? null,
            'mail_password' => $validated['mail_password'] ?? null,
            'mail_from_address' => $validated['mail_from_address'] ?? null,
            'mail_from_name' => $validated['mail_from_name'] ?? null,
        ];

        foreach ($fields as $key => $value) {
            $this->service->set($key, $value);
        }

        // Update Laravel mail config dynamically
        Config::set('mail.default', $validated['mail_mailer']);
        Config::set('mail.mailers.smtp.scheme', $mailScheme);
        Config::set('mail.mailers.smtp.host', $validated['mail_host']);
        Config::set('mail.mailers.smtp.port', $validated['mail_port']);
        Config::set('mail.mailers.smtp.username', $validated['mail_username']);
        Config::set('mail.mailers.smtp.password', $validated['mail_password']);
        Config::set('mail.from.address', $validated['mail_from_address']);
        Config::set('mail.from.name', $validated['mail_from_name']);

        if (function_exists('update_dotenv')) {
            update_dotenv([
                'MAIL_MAILER' => $validated['mail_mailer'],
                'MAIL_SCHEME' => $mailScheme,
                'MAIL_HOST' => $validated['mail_host'] ?? '',
                'MAIL_PORT' => $validated['mail_port'] ?? '',
                'MAIL_USERNAME' => $validated['mail_username'] ?? '',
                'MAIL_PASSWORD' => $validated['mail_password'] ?? '',
                'MAIL_FROM_ADDRESS' => $validated['mail_from_address'] ?? '',
                'MAIL_FROM_NAME' => $validated['mail_from_name'] ?? '',
            ]);
        }

        $this->service->forgetCache();

        if ($request->boolean('send_test_email')) {
            $recipient = $validated['test_email_recipient'] ?: Auth::user()?->email;

            try {
                Mail::mailer($validated['mail_mailer'])->to($recipient)->send(new class($validated) extends Mailable {
                    public function __construct(protected array $settings)
                    {
                    }

                    public function build(): self
                    {
                        if (! empty($this->settings['mail_from_address'])) {
                            $this->from($this->settings['mail_from_address'], $this->settings['mail_from_name'] ?? null);
                        }

                        return $this->subject('Invoice App Test Email')
                            ->html('<p>This is a test email from your Invoice app settings.</p><p>If you received this message, your mail settings are configured correctly.</p>');
                    }
                });
            } catch (\Throwable $exception) {
                return back()->withErrors(['send_test_email' => 'Unable to send test email: '.$exception->getMessage()]);
            }

            return redirect()->route('settings.index')->with('status', 'Email settings updated and test email sent to '.($recipient ?: 'your inbox').'.');
        }

        return redirect()->route('settings.index')->with('status', 'Email settings updated successfully.');
    }

    public function updatePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'upi_id' => ['nullable', 'string', 'max:64'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:64'],
            'ifsc_code' => ['nullable', 'string', 'max:15'],
        ]);

        $fields = [
            'upi_id' => $validated['upi_id'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'ifsc_code' => $validated['ifsc_code'] ?? null,
        ];

        foreach ($fields as $key => $value) {
            $this->service->set($key, $value);
        }

        $this->service->forgetCache();

        return redirect()->route('settings.index')->with('status', 'Payment settings updated successfully.');
    }

    private function calculateProgress(array $settings): array
    {
        $sections = [
            'business' => $this->isBusinessComplete($settings),
            'invoice' => $this->isInvoiceComplete($settings),
            'email' => $this->isEmailComplete($settings),
            'payment' => $this->isPaymentComplete($settings),
        ];

        $completed = array_sum(array_map(fn($complete) => $complete ? 1 : 0, $sections));
        $total = count($sections);
        $percentage = round(($completed / $total) * 100);

        return [
            'sections' => $sections,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    private function isBusinessComplete(array $settings): bool
    {
        return !empty($settings['business_name']) &&
               !empty($settings['gstin']) &&
               (!empty($settings['business_logo']) || !empty($settings['logo']));
    }

    private function isInvoiceComplete(array $settings): bool
    {
        return !empty($settings['invoice_prefix']) &&
               isset($settings['default_due_days']) &&
               isset($settings['default_gst_rate']) &&
               !empty($settings['currency']);
    }

    private function isEmailComplete(array $settings): bool
    {
        return !empty($settings['mail_host']) &&
               !empty($settings['mail_username']) &&
               !empty($settings['mail_from_address']) &&
               !empty($settings['mail_from_name']);
    }

    private function isPaymentComplete(array $settings): bool
    {
        return !empty($settings['upi_id']) ||
               (!empty($settings['bank_name']) && !empty($settings['account_number']) && !empty($settings['ifsc_code']));
    }

    private function storageImageDataUri(?string $path): ?string
    {
        $path = $this->normalizeStoragePath($path);

        if (empty($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/png';
        $contents = Storage::disk('public')->get($path);

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    private function normalizeStoragePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        return ltrim(preg_replace('#^/?storage/#', '', $path), '/');
    }
}
