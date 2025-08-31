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
        Schema::create('restaurant_modifiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('restaurant_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('restaurant_id');
            $table->index('status');
            $table->index('name');

            // Foreign key
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');

            // Unique constraint on restaurant_id and name
            $table->unique(['restaurant_id', 'name'], 'restaurant_modifiers_restaurant_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_modifiers');
    }
};

