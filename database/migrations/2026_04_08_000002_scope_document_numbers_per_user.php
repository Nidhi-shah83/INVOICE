<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropUnique('quotes_quote_number_unique');
            $table->unique(['user_id', 'quote_number']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_order_number_unique');
            $table->unique(['user_id', 'order_number']);
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_invoice_number_unique');
            $table->unique(['user_id', 'invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropUnique('quotes_user_id_quote_number_unique');
            $table->unique('quote_number');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_user_id_order_number_unique');
            $table->unique('order_number');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_user_id_invoice_number_unique');
            $table->unique('invoice_number');
        });
    }
};
