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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->string('cuisine_type')->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // Opening hours (JSON format)
            $table->json('opening_hours')->nullable();
            
            // Delivery settings
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('minimum_order', 8, 2)->default(0);
            $table->integer('min_delivery_time')->default(30); // minutes
            $table->integer('max_delivery_time')->default(60); // minutes
            $table->boolean('delivery_available')->default(true);
            $table->boolean('pickup_available')->default(true);
            $table->decimal('delivery_radius', 8, 2)->nullable(); // km
            
            // Restaurant status
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            
            // Ratings and reviews
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            
            // Admin assignment
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->unsignedBigInteger('assigned_manager_id')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'is_featured'], 'restaurants_status_featured_idx');
            $table->index(['latitude', 'longitude'], 'restaurants_location_idx');
            $table->index('assigned_admin_id');
            $table->index('assigned_manager_id');
            
            // Foreign keys
            $table->foreign('assigned_admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
