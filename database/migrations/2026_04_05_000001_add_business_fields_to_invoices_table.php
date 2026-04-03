<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('discount_type', ['flat', 'percent'])->default('flat')->after('status');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('round_off', 12, 2)->default(0)->after('total');
            $table->decimal('grand_total', 12, 2)->default(0)->after('round_off');
            $table->decimal('amount_paid', 12, 2)->default(0)->after('grand_total');
            $table->decimal('amount_due', 12, 2)->default(0)->after('amount_paid');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('amount_due');
            $table->enum('invoice_type', ['tax', 'proforma'])->default('tax')->after('payment_status');
            $table->string('currency', 8)->default('INR')->after('invoice_type');
            $table->string('po_number')->nullable()->after('currency');
            $table->string('reference_no')->nullable()->after('po_number');
            $table->string('payment_terms')->nullable()->after('reference_no');
            $table->text('terms_conditions')->nullable()->after('payment_terms');
            $table->string('bank_name')->nullable()->after('terms_conditions');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('ifsc_code')->nullable()->after('account_number');
            $table->string('upi_id')->nullable()->after('ifsc_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_amount',
                'round_off',
                'grand_total',
                'amount_paid',
                'amount_due',
                'payment_status',
                'invoice_type',
                'currency',
                'po_number',
                'reference_no',
                'payment_terms',
                'terms_conditions',
                'bank_name',
                'account_number',
                'ifsc_code',
                'upi_id',
            ]);
        });
    }
};
