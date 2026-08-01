<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Front Office',
            'Housekeeping',
            'Food & Beverage Service',
            'Food & Beverage Kitchen',
            'Sales & Marketing',
            'Human Resources',
            'Finance & Accounting',
            'Engineering & Maintenance',
            'IT',
            'Security',
            'Spa & Recreation',
            'Purchasing & Store',
        ];

        foreach ($departments as $name) {
            Department::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
