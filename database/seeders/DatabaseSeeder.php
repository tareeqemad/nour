<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            ConstantSeeder::class,
            SettingsSeeder::class,
            WelcomeMessageSeeder::class,
            SmsTemplateSeeder::class,
            UserSeeder::class,
            TestDataSeeder::class,
            TechnicianCivilDefenseSeeder::class, // يوزر فني + يوزر دفاع مدني
            TestUsersSeeder::class,
            CreateDefaultOperatorRolesSeeder::class, // إنشاء أدوار افتراضية للمشغلين
            MinimumChargeRuleSeeder::class, // قواعد الحد الأدنى لكل مشغل
            // Note: OperatorSeeder, AuthorizedPhoneSeeder, ComplaintSuggestionSeeder, MessageSeeder are not called
            // Only basic seeders (permissions, roles, constants, settings, messages, users) are seeded
        ]);
    }
}
