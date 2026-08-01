<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')->constrained('training_modules')->restrictOnDelete();
            $table->string('trainer_name', 150);
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('actual_duration_hours', 4, 1)->nullable();
            $table->string('location', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('session_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
