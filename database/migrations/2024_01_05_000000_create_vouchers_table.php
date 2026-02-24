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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique voucher code
            $table->text('description');
            $table->integer('discount_amount')->nullable(); // In cents (fixed discount)
            $table->integer('discount_percentage')->nullable(); // Percentage discount (1-100)
            $table->integer('max_uses')->nullable(); // NULL for unlimited
            $table->integer('current_uses')->default(0);
            $table->integer('usage_per_user')->default(1); // How many times a user can use it
            $table->boolean('is_active')->default(true);
            $table->datetime('expires_at');
            $table->datetime('starts_at'); // When voucher becomes active
            $table->integer('minimum_purchase_amount')->default(0); // In cents
            $table->enum('applicable_to', ['all', 'repaint_only', 'general_only'])->default('all');
            $table->json('applicable_services')->nullable(); // Specific service IDs if needed
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for voucher lookup and expiry checks
            $table->index('code');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
