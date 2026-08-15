<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // ==== Demografi personal ====
            $table->string('place_of_birth', 100)->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('place_of_birth');
            // "Age" & "Month of Birthday" SENGAJA tidak disimpan — dihitung otomatis
            // dari date_of_birth via accessor, supaya tidak pernah basi.
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->string('religion', 50)->nullable()->after('gender');
            $table->string('blood_type', 5)->nullable()->after('religion');
            $table->string('marital_status_tax', 30)->nullable()->after('blood_type'); // "Merital Status (Tax)"
            $table->string('job_level', 30)->nullable()->after('marital_status_tax'); // "Level"
            $table->text('address')->nullable()->after('job_level');
            $table->string('region', 100)->nullable()->after('address'); // "Daerah"
            $table->unsignedTinyInteger('annual_leave_entitlement')->nullable()->after('region'); // "AL Entitlement"

            // ==== Data keluarga ====
            $table->string('spouse_name', 150)->nullable()->after('annual_leave_entitlement');
            $table->date('spouse_date_of_birth')->nullable()->after('spouse_name');
            $table->unsignedTinyInteger('children_count')->nullable()->after('spouse_date_of_birth');
            $table->string('emergency_contact_name', 150)->nullable()->after('children_count');
            $table->string('emergency_contact_relationship', 50)->nullable()->after('emergency_contact_name');

            // ==== Finansial / legal (SENSITIF — akses ke data ini sebaiknya dibatasi) ====
            $table->string('npwp_no', 30)->nullable()->after('emergency_contact_relationship');
            $table->string('bank_account_number', 30)->nullable()->after('npwp_no');
            $table->string('bank_account_name', 150)->nullable()->after('bank_account_number'); // "A/N"
            // NIK KTP asli — TERPISAH dari kolom `nik` yang sudah ada (itu sebenarnya "ID No." badge).
            $table->string('nik_ktp', 20)->nullable()->after('bank_account_name');
            $table->string('jamsostek_no', 30)->nullable()->after('nik_ktp');
            $table->string('bpjs_no', 30)->nullable()->after('jamsostek_no');

            // ==== Pendidikan ====
            $table->string('education_background', 150)->nullable()->after('bpjs_no');
            $table->string('education_level', 30)->nullable()->after('education_background');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'place_of_birth', 'date_of_birth', 'gender', 'religion', 'blood_type',
                'marital_status_tax', 'job_level', 'address', 'region', 'annual_leave_entitlement',
                'spouse_name', 'spouse_date_of_birth', 'children_count',
                'emergency_contact_name', 'emergency_contact_relationship',
                'npwp_no', 'bank_account_number', 'bank_account_name', 'nik_ktp',
                'jamsostek_no', 'bpjs_no', 'education_background', 'education_level',
            ]);
        });
    }
};
