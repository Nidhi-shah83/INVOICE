<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/invoice', [OrderController::class, 'createInvoice'])->name('orders.createInvoice');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/overdue', [InvoiceController::class, 'overdue'])->name('invoices.overdue');
    Route::patch('invoices/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
    Route::post('invoices/{invoice}/paid/manual', [InvoiceController::class, 'markPaidManually'])->name('invoices.markPaidManual');
    Route::resource('ai-assistant', AiAssistantController::class)->parameters(['ai-assistant' => 'aiAssistant']);
    Route::get('ai-assistant', [AIController::class, 'chat'])->name('ai-assistant.index');
    Route::get('ai-assistant/chat', [AIController::class, 'chat'])->name('ai-assistant.chat');
    Route::post('ai-assistant/parse', [AIController::class, 'parse'])->name('ai-assistant.parse');
    Route::resource('reports', ReportController::class);
    Route::resource('settings', SettingController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('quotes/accept/{token}', [QuoteController::class, 'accept'])->name('quotes.accept');
require __DIR__.'/auth.php';
