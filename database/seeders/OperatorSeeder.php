<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على مستخدم المشغل (البابا)
        $companyOwner = User::where('role', Role::CompanyOwner->value)
            ->where('username', 'like', 'co_%')
            ->first();

        if (!$companyOwner) {
            $this->command->warn('لم يتم العثور على مستخدم المشغل (البابا). تأكد من تشغيل UserSeeder أولاً.');
            return;
        }

        // التحقق من عدم وجود المشغل مسبقاً
        $existingOperator = Operator::where('owner_id', $companyOwner->id)->first();
        
        if (!$existingOperator) {
            $operator = Operator::create([
                'name' => 'البابا',
                'name_en' => 'Al Baba',
                'owner_id' => $companyOwner->id,
                'status' => 'active',
                'is_approved' => true,
                'profile_completed' => false,
            ]);

            $this->command->info("تم إنشاء المشغل '{$operator->name}' بنجاح للمستخدم '{$companyOwner->name}'");
        } else {
            $this->command->info("المشغل '{$existingOperator->name}' موجود بالفعل للمستخدم '{$companyOwner->name}'");
        }
    }
}


