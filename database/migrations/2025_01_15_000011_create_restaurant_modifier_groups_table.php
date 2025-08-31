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
        Schema::create('restaurant_modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('selection_type', ['required', 'optional'])->default('optional');
            $table->integer('required_count')->nullable()->comment('Number of required selections when type is required');
            $table->unsignedBigInteger('restaurant_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('restaurant_id');
            $table->index('status');
            $table->index('name');
            $table->index('selection_type');

            // Foreign key
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');

            // Unique constraint on restaurant_id and name
            $table->unique(['restaurant_id', 'name'], 'restaurant_modifier_groups_restaurant_name_unique');
        });

        // Pivot table for many-to-many relationship between modifier groups and modifiers
        Schema::create('restaurant_modifier_group_modifier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modifier_group_id');
            $table->unsignedBigInteger('modifier_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('modifier_group_id');
            $table->index('modifier_id');
            $table->index('sort_order');

            // Foreign keys
            $table->foreign('modifier_group_id')->references('id')->on('restaurant_modifier_groups')->onDelete('cascade');
            $table->foreign('modifier_id')->references('id')->on('restaurant_modifiers')->onDelete('cascade');

            // Unique constraint to prevent duplicate relationships
            $table->unique(['modifier_group_id', 'modifier_id'], 'modifier_group_modifier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_modifier_group_modifier');
        Schema::dropIfExists('restaurant_modifier_groups');
    }
};

