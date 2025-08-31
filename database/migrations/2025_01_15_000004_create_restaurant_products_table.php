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
        Schema::create('restaurant_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            
            // Product details
            $table->text('ingredients')->nullable();
            $table->text('allergens')->nullable();
            $table->integer('preparation_time')->nullable(); // minutes
            $table->integer('calories')->nullable();
            $table->string('dietary_info')->nullable(); // vegetarian, vegan, gluten-free, etc.
            
            // Availability
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_recommended')->default(false);
            
            // Stock management
            $table->integer('stock_quantity')->nullable();
            $table->boolean('track_stock')->default(false);
            $table->boolean('allow_out_of_stock_orders')->default(false);
            
            // Customization
            $table->boolean('allow_customization')->default(false);
            $table->json('customization_options')->nullable();
            
            // Sorting and display
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('order_count')->default(0);
            
            // Ratings
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('restaurant_id');
            $table->index('category_id');
            $table->index('subcategory_id');
            $table->index(['restaurant_id', 'category_id'], 'rest_prod_rest_cat_idx');
            $table->index(['restaurant_id', 'is_available'], 'rest_prod_rest_available_idx');
            $table->index(['restaurant_id', 'is_featured'], 'rest_prod_rest_featured_idx');
            $table->index('price');
            $table->index('sort_order');
            
            // Foreign keys
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('restaurant_categories')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('restaurant_subcategories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_products');
    }
};
