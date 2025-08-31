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
        Schema::create('restaurant_subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('icon_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('restaurant_id');
            $table->index('category_id');
            $table->index(['restaurant_id', 'category_id'], 'rest_subcat_rest_cat_idx');
            $table->index(['restaurant_id', 'category_id', 'is_active'], 'rest_subcat_rest_cat_active_idx');
            $table->index('sort_order');
            
            // Foreign keys
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('restaurant_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_subcategories');
    }
};
