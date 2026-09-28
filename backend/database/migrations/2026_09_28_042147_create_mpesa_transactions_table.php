<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->decimal('amount', 10, 2);

            // From STK Push request
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable()->unique();

            // From callback
            $table->integer('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->string('mpesa_receipt')->nullable()->index();
            $table->timestamp('transaction_date')->nullable();

            $table->enum('status', ['initiated', 'success', 'failed', 'cancelled', 'timeout'])
                ->default('initiated')
                ->index();

            $table->json('raw_response')->nullable();
            $table->json('raw_callback')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
