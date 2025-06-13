<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users');
            $table->foreignId('surveyor_id')->nullable()->constrained('users');
            $table->foreignId('constructor_id')->nullable()->constrained('users');
            $table->foreignId('installer_id')->nullable()->constrained('users');
            $table->string('customer_name');
            $table->string('address');
            $table->string('phone_number');
            $table->integer('order_number')->unique();
            $table->string('email')->nullable();
            $table->timestamp('meeting_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('product_name')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Add soft deletes
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
