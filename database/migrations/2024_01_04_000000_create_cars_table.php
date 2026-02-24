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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('color');
            $table->string('license_plate')->unique();
            $table->text('description');
            $table->integer('price'); // In cents
            $table->integer('mileage'); // In km
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric'])->default('petrol');
            $table->enum('transmission', ['manual', 'automatic'])->default('automatic');
            $table->json('images')->nullable(); // Array of Cloudinary image URLs
            $table->json('features')->nullable(); // Air conditioning, GPS, etc
            $table->enum('status', ['available', 'sold', 'pending'])->default('available');
            $table->integer('views_count')->default(0);
            $table->timestamp('listed_at');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for search and filtering
            $table->index('seller_id');
            $table->index('brand');
            $table->index('year');
            $table->index('price');
            $table->index('status');
            $table->index('fuel_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
