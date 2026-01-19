<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Operator;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تم تعطيل إنشاء المستخدمين التجريبيين
        // SuperAdmin: sp_admin / 123456
        // Admin: a_admin / 123456
        // Energy Authority: ea_authority / 123456
        // CompanyOwner: op_owner / 123456
        // Employee: emp_user / 123456
        // Technician: t_technician / 123456
        // CivilDefense: cd_defense / 123456
        return;
    }
}
