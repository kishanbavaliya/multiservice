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
        Schema::create('restaurant_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url');
            $table->enum('banner_type', ['homepage', 'offers', 'promotions', 'featured', 'sidebar', 'popup'])->default('homepage');
            $table->integer('position')->default(1); // 1=Top, 2=Middle, 3=Bottom, 4=Left Sidebar, 5=Right Sidebar, 6=Full Width
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('target_blank')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('click_count')->default(0);
            $table->integer('impression_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['restaurant_id', 'banner_type']);
            $table->index(['restaurant_id', 'position']);
            $table->index(['restaurant_id', 'is_active']);
            $table->index(['banner_type', 'is_active']);
            $table->index('sort_order');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_banners');
    }
};

