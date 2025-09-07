<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartModifiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained('cart_items')->onDelete('cascade');
            $table->unsignedBigInteger('modifier_id')->nullable();
            $table->unsignedBigInteger('modifier_group_id')->nullable();
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->string('name')->nullable();
            $table->double('price', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
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
        Schema::dropIfExists('cart_modifiers');
    }
}
