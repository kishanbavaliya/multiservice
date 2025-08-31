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
        Schema::create('restaurant_user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['super_admin', 'restaurant_admin', 'restaurant_manager', 'staff'])->default('staff');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('assigned_at');
            $table->dateTime('expires_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('restaurant_id');
            $table->index('user_id');
            $table->index(['restaurant_id', 'user_id']);
            $table->index(['restaurant_id', 'role']);
            $table->index(['user_id', 'is_active']);
            
            // Foreign keys
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint
            $table->unique(['restaurant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_user_roles');
    }
};
