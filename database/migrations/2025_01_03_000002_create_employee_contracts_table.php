<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence'); // urutan kontrak: 1, 2, 3, ...
            $table->enum('type', ['permanent', 'contract', 'jeda'])->default('contract');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_last')->default(false); // menandai "Last Contract" dari data Excel
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
