<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            // reference to restaurant_products (canonical product for cart)
            $table->unsignedBigInteger('restaurant_product_id')->nullable();
            $table->foreign('restaurant_product_id')->references('id')->on('restaurant_products')->onDelete('set null');
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('set null');
            $table->unsignedBigInteger('restaurant_category_id')->nullable();
            $table->unsignedBigInteger('restaurant_subcategory_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->double('price', 15, 2)->default(0); // unit price at time added
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
