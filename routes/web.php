<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('quotes', QuoteController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('ai-assistant', AiAssistantController::class);
    Route::resource('reports', ReportController::class);
    Route::resource('settings', SettingController::class);
});

require __DIR__.'/auth.php';
