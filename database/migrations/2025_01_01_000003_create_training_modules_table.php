<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('category', 100)->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->decimal('standard_duration_hours', 4, 1)->nullable();
            $table->unsignedInteger('validity_months')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_mandatory');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_modules');
    }
};
