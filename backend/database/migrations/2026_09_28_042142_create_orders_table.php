<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // BP-XXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 8)->default('KES');

            $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'refunded', 'cancelled'])
                ->default('pending')
                ->index();
            $table->enum('payment_method', ['mpesa', 'card', 'paypal', 'manual'])->default('mpesa');
            $table->enum('payment_status', ['pending', 'processing', 'paid', 'failed', 'refunded'])
                ->default('pending')
                ->index();

            $table->boolean('needs_shipping')->default(false);
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
