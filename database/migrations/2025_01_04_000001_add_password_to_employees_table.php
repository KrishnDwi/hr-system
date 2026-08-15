<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Login karyawan TERPISAH dari login HRD (guard berbeda, lihat
            // config/auth.php). Password diisi oleh HRD lewat form Edit Karyawan
            // — belum ada self-registration di v1 ini.
            $table->string('password')->nullable()->after('phone');
            $table->rememberToken()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token']);
        });
    }
};
