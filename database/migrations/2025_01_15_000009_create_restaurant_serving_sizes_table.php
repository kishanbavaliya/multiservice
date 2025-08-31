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
        Schema::create('restaurant_serving_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['restaurant_id', 'status']);
            $table->index('name');
            $table->index('status');
            
            // Unique constraint for name per restaurant (or global)
            $table->unique(['restaurant_id', 'name'], 'unique_serving_size_per_restaurant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_serving_sizes');
    }
};

