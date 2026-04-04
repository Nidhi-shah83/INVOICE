<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->index();
            $table->date('promised_payment_date')->nullable();
            $table->string('confidence')->nullable();
            $table->text('notes')->nullable();
            $table->longText('conversation')->nullable();
            $table->timestamp('call_started_at')->nullable();
            $table->timestamp('call_ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_call_logs');
    }
};
