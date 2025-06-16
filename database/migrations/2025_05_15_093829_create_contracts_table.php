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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('constructor_id')->nullable()->constrained('users');
            $table->string('contract_number')->nullable()->unique();
            $table->date('signed_at')->nullable();
            $table->text('comment')->nullable();
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->string('product_type')->nullable();
            $table->date('ready_date')->nullable();
            $table->date('documentation_due_at')->nullable();
            $table->date('installation_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
