<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Menentukan kategori pekerja — relevan untuk ETMS karena menentukan
            // cakupan mandatory training (mis. DW/Casual/Outsourcing tetap wajib
            // Fire Safety, tapi belum tentu wajib training khusus staf tetap).
            $table->enum('employee_type', ['staff', 'dw', 'casual', 'trainee', 'outsourcing'])
                ->default('staff')
                ->after('employment_status');

            $table->index('employee_type');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_type');
        });
    }
};
