<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'mail.settings'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'downloadPdf'])->name('quotes.pdf');
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');

    Route::resource('orders', OrderController::class);
    Route::get('orders/{order}/pdf', [OrderController::class, 'downloadPdf'])->name('orders.pdf');
    Route::post('orders/{order}/send-pdf', [OrderController::class, 'sendPdf'])->name('orders.sendPdf');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/invoice', [OrderController::class, 'createInvoice'])->name('orders.createInvoice');

    Route::get('invoices/search-suggestions', [InvoiceController::class, 'searchSuggestions'])->name('invoices.searchSuggestions');
    Route::get('invoices/{invoice_number}/call-logs', [InvoiceController::class, 'getCallLogs'])->name('invoices.callLogs');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/overdue', [InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::patch('invoices/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::post('invoices/{invoice}/paid/manual', [InvoiceController::class, 'markPaidManually'])->name('invoices.markPaidManual');
    Route::get('/pay-invoice/{invoice}', [InvoiceController::class, 'showPaymentPage'])->name('invoices.pay');
    Route::post('/pay-invoice/{invoice}', [InvoiceController::class, 'processPayment'])->name('invoices.pay.process');
    Route::view('/payment-success', 'payments.success')->name('payment.success');

    Route::get('ai-assistant', [AIController::class, 'chat'])->name('ai-assistant.index');
    Route::get('ai-assistant/chat', [AIController::class, 'chat'])->name('ai-assistant.chat');
    Route::post('ai-assistant/parse', [AIController::class, 'parse'])->name('ai-assistant.parse');

    Route::resource('reports', ReportController::class);
    Route::resource('products', ProductController::class)->only(['index', 'store', 'destroy', 'edit', 'update']);
    Route::get('reports-export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('settings/business', [SettingController::class, 'updateBusiness'])->name('settings.update.business');
    Route::patch('settings/invoice', [SettingController::class, 'updateInvoice'])->name('settings.update.invoice');
    Route::patch('settings/email', [SettingController::class, 'updateEmail'])->name('settings.update.email');
    Route::patch('settings/payment', [SettingController::class, 'updatePayment'])->name('settings.update.payment');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('quotes/accept/{token}', [QuoteController::class, 'accept'])
    ->middleware('auth')
    ->name('quotes.accept');
require __DIR__.'/auth.php';
