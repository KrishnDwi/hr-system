<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->enum('attendance_status', ['present', 'absent', 'excused'])->default('present');
            $table->timestamps();

            $table->unique(['training_session_id', 'employee_id'], 'uniq_session_employee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_participants');
    }
};
