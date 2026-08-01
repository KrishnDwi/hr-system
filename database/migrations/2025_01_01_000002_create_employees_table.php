<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 30)->unique();
            $table->string('name', 150);
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('position', 100)->nullable();
            $table->date('join_date')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'resigned'])->default('active');
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'employment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
