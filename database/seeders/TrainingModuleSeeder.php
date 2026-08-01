<?php

namespace Database\Seeders;

use App\Models\TrainingModule;
use Illuminate\Database\Seeder;

class TrainingModuleSeeder extends Seeder
{
    /**
     * 30 modul training contoh, disesuaikan konteks hospitality/hotel.
     * 12 di antaranya ditandai mandatory — sesuai requirement bisnis Anda,
     * namun jumlah ini TIDAK di-hardcode di kode manapun; murni data.
     */
    public function run(): void
    {
        $modules = [
            // ==== MANDATORY (12) ====
            ['code' => 'MDT-01', 'name' => 'Fire Safety & Prevention', 'category' => 'Safety & Security', 'is_mandatory' => true, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'MDT-02', 'name' => 'Emergency Evacuation Procedure', 'category' => 'Safety & Security', 'is_mandatory' => true, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'MDT-03', 'name' => 'Occupational Health & Safety Awareness', 'category' => 'Safety & Security', 'is_mandatory' => true, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'MDT-04', 'name' => 'First Aid & CPR Basic', 'category' => 'Safety & Security', 'is_mandatory' => true, 'standard_duration_hours' => 4, 'validity_months' => 24],
            ['code' => 'MDT-05', 'name' => 'Food Hygiene & HACCP Basic', 'category' => 'Food & Beverage', 'is_mandatory' => true, 'standard_duration_hours' => 3, 'validity_months' => 12],
            ['code' => 'MDT-06', 'name' => 'Chemical Handling & MSDS', 'category' => 'Safety & Security', 'is_mandatory' => true, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'MDT-07', 'name' => 'Manual Handling & Ergonomics', 'category' => 'Safety & Security', 'is_mandatory' => true, 'standard_duration_hours' => 1.5, 'validity_months' => 12],
            ['code' => 'MDT-08', 'name' => 'Code of Conduct & Anti-Harassment', 'category' => 'Compliance', 'is_mandatory' => true, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'MDT-09', 'name' => 'Data Privacy & Guest Confidentiality', 'category' => 'Compliance', 'is_mandatory' => true, 'standard_duration_hours' => 1.5, 'validity_months' => 12],
            ['code' => 'MDT-10', 'name' => 'Anti-Bribery & Anti-Corruption', 'category' => 'Compliance', 'is_mandatory' => true, 'standard_duration_hours' => 1.5, 'validity_months' => 12],
            ['code' => 'MDT-11', 'name' => 'Grooming & Guest Service Standard', 'category' => 'Service Excellence', 'is_mandatory' => true, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'MDT-12', 'name' => 'New Employee Orientation (Induction)', 'category' => 'HR', 'is_mandatory' => true, 'standard_duration_hours' => 4, 'validity_months' => null],

            // ==== NON MANDATORY (18) ====
            ['code' => 'OPT-01', 'name' => 'Handling Guest Complaints Effectively', 'category' => 'Service Excellence', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => 24],
            ['code' => 'OPT-02', 'name' => 'Front Office SOP & Check-in/out Procedure', 'category' => 'Front Office', 'is_mandatory' => false, 'standard_duration_hours' => 3, 'validity_months' => 24],
            ['code' => 'OPT-03', 'name' => 'Housekeeping Room Cleaning Standard', 'category' => 'Housekeeping', 'is_mandatory' => false, 'standard_duration_hours' => 3, 'validity_months' => 24],
            ['code' => 'OPT-04', 'name' => 'F&B Table Service Standard', 'category' => 'Food & Beverage', 'is_mandatory' => false, 'standard_duration_hours' => 3, 'validity_months' => 24],
            ['code' => 'OPT-05', 'name' => 'Bar & Beverage Service Basics', 'category' => 'Food & Beverage', 'is_mandatory' => false, 'standard_duration_hours' => 3, 'validity_months' => 24],
            ['code' => 'OPT-06', 'name' => 'Cash Handling & Cashiering Procedure', 'category' => 'Finance', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => 24],
            ['code' => 'OPT-07', 'name' => 'Property Management System (Opera) Basic', 'category' => 'IT & Systems', 'is_mandatory' => false, 'standard_duration_hours' => 4, 'validity_months' => null],
            ['code' => 'OPT-08', 'name' => 'POS System Operation', 'category' => 'IT & Systems', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => null],
            ['code' => 'OPT-09', 'name' => 'CCTV & Security Monitoring Procedure', 'category' => 'Safety & Security', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => 24],
            ['code' => 'OPT-10', 'name' => 'Legionella & Water Quality Awareness', 'category' => 'Safety & Security', 'is_mandatory' => false, 'standard_duration_hours' => 1.5, 'validity_months' => 24],
            ['code' => 'OPT-11', 'name' => 'Work at Height Safety', 'category' => 'Safety & Security', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => 12],
            ['code' => 'OPT-12', 'name' => 'Environmental Sustainability Practice', 'category' => 'Sustainability', 'is_mandatory' => false, 'standard_duration_hours' => 1.5, 'validity_months' => 24],
            ['code' => 'OPT-13', 'name' => 'Leadership Skill for Supervisor', 'category' => 'Leadership', 'is_mandatory' => false, 'standard_duration_hours' => 6, 'validity_months' => null],
            ['code' => 'OPT-14', 'name' => 'Train the Trainer (TWI Method)', 'category' => 'Leadership', 'is_mandatory' => false, 'standard_duration_hours' => 6, 'validity_months' => null],
            ['code' => 'OPT-15', 'name' => 'Effective Communication Skill', 'category' => 'Soft Skill', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => null],
            ['code' => 'OPT-16', 'name' => 'Time Management & Productivity', 'category' => 'Soft Skill', 'is_mandatory' => false, 'standard_duration_hours' => 2, 'validity_months' => null],
            ['code' => 'OPT-17', 'name' => 'Basic English for Hospitality', 'category' => 'Soft Skill', 'is_mandatory' => false, 'standard_duration_hours' => 8, 'validity_months' => null],
            ['code' => 'OPT-18', 'name' => 'Revenue Management Basic Concept', 'category' => 'Sales & Marketing', 'is_mandatory' => false, 'standard_duration_hours' => 3, 'validity_months' => null],

        ];

        foreach ($modules as $module) {
            TrainingModule::updateOrCreate(
                ['code' => $module['code']],
                array_merge($module, ['is_active' => true])
            );
        }
    }
}
