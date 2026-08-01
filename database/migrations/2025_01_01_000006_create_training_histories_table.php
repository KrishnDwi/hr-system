<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('training_participant_id')->nullable()
                ->constrained('training_participants')->nullOnDelete();
            $table->foreignId('training_module_id')->nullable()
                ->constrained('training_modules')->nullOnDelete();

            // Snapshot fields — sengaja tidak mengikuti perubahan master di masa depan
            $table->string('training_code_snapshot', 30);
            $table->string('training_name_snapshot', 150);
            $table->boolean('is_mandatory_snapshot')->default(false);
            $table->string('trainer_name_snapshot', 150);
            $table->date('training_date');
            $table->decimal('duration_hours_snapshot', 4, 1)->nullable();
            $table->unsignedInteger('validity_months_snapshot')->nullable();
            $table->date('expired_at')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'expired_at']);
            $table->index(['training_module_id', 'expired_at']);
            $table->index('is_mandatory_snapshot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_histories');
    }
};
