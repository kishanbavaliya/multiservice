<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantProductModifierGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_product_modifier_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_product_id')->index();
            $table->unsignedBigInteger('modifier_group_id')->index();
            $table->timestamps();

            $table->foreign('restaurant_product_id')->references('id')->on('restaurant_products')->onDelete('cascade');
            $table->foreign('modifier_group_id')->references('id')->on('restaurant_modifier_groups')->onDelete('cascade');
            $table->unique(['restaurant_product_id', 'modifier_group_id'], 'restaurant_product_modifier_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurant_product_modifier_group');
    }
}
