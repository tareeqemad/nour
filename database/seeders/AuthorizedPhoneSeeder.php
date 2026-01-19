<?php

namespace Database\Seeders;

use App\Models\AuthorizedPhone;
use Illuminate\Database\Seeder;

class AuthorizedPhoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // جميع الأرقام المصرح بها
        $phones = [
            ['phone' => '0562200957', 'name' => 'مولدات أبو لبدة للطاقة البديلة', 'notes' => null, 'is_active' => true],
            ['phone' => '0566450450', 'name' => 'مولدات سمور الشاطئ', 'notes' => null, 'is_active' => true],
            ['phone' => '0566500073', 'name' => 'مولدات أبو عيسى', 'notes' => null, 'is_active' => true],
            ['phone' => '0568636686', 'name' => 'مولدات صلاح الدين للكهرباء والطاقة البديلة', 'notes' => null, 'is_active' => true],
            ['phone' => '0592010242', 'name' => 'مولدات إبن المخيم', 'notes' => null, 'is_active' => true],
            ['phone' => '0592162888', 'name' => 'مولدات صيام', 'notes' => null, 'is_active' => true],
            ['phone' => '0592200835', 'name' => 'مولدات عبد الرحمن', 'notes' => null, 'is_active' => true],
            ['phone' => '0592203011', 'name' => 'مولد ابو شمله', 'notes' => null, 'is_active' => true],
            ['phone' => '0592247882', 'name' => 'مولدات وافي البلد', 'notes' => null, 'is_active' => true],
            ['phone' => '0592255966', 'name' => 'مولدات المدينة', 'notes' => null, 'is_active' => true],
            ['phone' => '0592303708', 'name' => 'مولدات أبو نحل', 'notes' => null, 'is_active' => true],
            ['phone' => '0592333367', 'name' => 'مولدات الشرفا', 'notes' => null, 'is_active' => true],
            ['phone' => '0592409847', 'name' => 'مولد الممولك', 'notes' => null, 'is_active' => true],
            ['phone' => '0592597200', 'name' => 'مولدات المنايعة', 'notes' => null, 'is_active' => true],
            ['phone' => '0592632026', 'name' => 'مولد البواب', 'notes' => null, 'is_active' => true],
            ['phone' => '0592639197', 'name' => 'مولدات ختام نصر', 'notes' => null, 'is_active' => true],
            ['phone' => '0592712000', 'name' => 'مولدات الحداد', 'notes' => null, 'is_active' => true],
            ['phone' => '0592740005', 'name' => 'مولدات هاوس باور', 'notes' => null, 'is_active' => true],
            ['phone' => '0593033310', 'name' => 'مولدات قشطة وشركائة', 'notes' => null, 'is_active' => true],
            ['phone' => '0593124142', 'name' => 'مولدات الشجاعية', 'notes' => null, 'is_active' => true],
            ['phone' => '0593400030', 'name' => 'مولدات أبو حطب', 'notes' => null, 'is_active' => true],
            ['phone' => '0594100693', 'name' => 'مولدات المجدﻻوي مخيم 2', 'notes' => null, 'is_active' => true],
            ['phone' => '0594309056', 'name' => 'مولدات نور فلسطين', 'notes' => null, 'is_active' => true],
            ['phone' => '0594395299', 'name' => 'مولدات الكحلوت جباليا', 'notes' => null, 'is_active' => true],
            ['phone' => '0594400044', 'name' => 'مولدات غزال 2', 'notes' => null, 'is_active' => true],
            ['phone' => '0594440007', 'name' => 'مولدات حجازي', 'notes' => null, 'is_active' => true],
            ['phone' => '0594784536', 'name' => 'مولدات نور البلد1', 'notes' => null, 'is_active' => true],
            ['phone' => '0595222282', 'name' => 'مولدات الرحمة', 'notes' => null, 'is_active' => true],
            ['phone' => '0595226002', 'name' => 'مولدات أبو زرقة', 'notes' => null, 'is_active' => true],
            ['phone' => '0595255539', 'name' => 'مولدات باريس', 'notes' => null, 'is_active' => true],
            ['phone' => '0595434913', 'name' => 'مولدات نور بيتك', 'notes' => null, 'is_active' => true],
            ['phone' => '0595476444', 'name' => 'مولدات البحيصي', 'notes' => null, 'is_active' => true],
            ['phone' => '0595479555', 'name' => 'مولدات الهريدي', 'notes' => null, 'is_active' => true],
            ['phone' => '0595625433', 'name' => 'مولدات عودة الكرامة', 'notes' => null, 'is_active' => true],
            ['phone' => '0595700897', 'name' => 'مولدات أبو خوصة النصيرات', 'notes' => null, 'is_active' => true],
            ['phone' => '0595711755', 'name' => 'مولدات الكتناني', 'notes' => null, 'is_active' => true],
            ['phone' => '0595844823', 'name' => 'مولدات صلاح', 'notes' => null, 'is_active' => true],
            ['phone' => '0595918655', 'name' => 'مولدات قنيطة', 'notes' => null, 'is_active' => true],
            ['phone' => '0595999998', 'name' => 'مولدات السموني', 'notes' => null, 'is_active' => true],
            ['phone' => '0597071692', 'name' => 'مولدات أنوار السلطان', 'notes' => null, 'is_active' => true],
            ['phone' => '0597197997', 'name' => 'مولدات الحمد', 'notes' => null, 'is_active' => true],
            ['phone' => '0597222217', 'name' => 'مولدات الرمال', 'notes' => null, 'is_active' => true],
            ['phone' => '0597297826', 'name' => 'مولدات الكتيبة', 'notes' => null, 'is_active' => true],
            ['phone' => '0597310303', 'name' => 'مولدات أبو هويشل', 'notes' => null, 'is_active' => true],
            ['phone' => '0597372729', 'name' => 'مولداتFG POWER', 'notes' => null, 'is_active' => true],
            ['phone' => '0597452022', 'name' => 'مولدات المدينة المخابرات', 'notes' => null, 'is_active' => true],
            ['phone' => '0597555583', 'name' => 'مولدات بركات الزيتون', 'notes' => null, 'is_active' => true],
            ['phone' => '0597555584', 'name' => 'مولدات بركات يافا', 'notes' => null, 'is_active' => true],
            ['phone' => '0597722605', 'name' => 'مولدات الهبيل', 'notes' => null, 'is_active' => true],
            ['phone' => '0597730112', 'name' => 'مولدات المصري فلسطين', 'notes' => null, 'is_active' => true],
            ['phone' => '0597732229', 'name' => 'مولدات المدينة1', 'notes' => null, 'is_active' => true],
            ['phone' => '0597739999', 'name' => 'مولدات الميدان', 'notes' => null, 'is_active' => true],
            ['phone' => '0597768215', 'name' => 'مولدات اﻷمل', 'notes' => null, 'is_active' => true],
            ['phone' => '0597770516', 'name' => 'مولدات مشتهى', 'notes' => null, 'is_active' => true],
            ['phone' => '0597780641', 'name' => 'مولدات الفا بور1', 'notes' => null, 'is_active' => true],
            ['phone' => '0597783717', 'name' => 'مولدات حارتنا النفق', 'notes' => null, 'is_active' => true],
            ['phone' => '0597789996', 'name' => 'مولدات بركات الرضوان', 'notes' => null, 'is_active' => true],
            ['phone' => '0597904043', 'name' => 'مولدات السﻻطين', 'notes' => null, 'is_active' => true],
            ['phone' => '0597904103', 'name' => 'مولدات المنار', 'notes' => null, 'is_active' => true],
            ['phone' => '0598076640', 'name' => 'مولدات النور للطاقة البديلة', 'notes' => null, 'is_active' => true],
            ['phone' => '0598267469', 'name' => 'مولداتات العودة', 'notes' => null, 'is_active' => true],
            ['phone' => '0598606611', 'name' => 'مولدات المشتل', 'notes' => null, 'is_active' => true],
            ['phone' => '0598775191', 'name' => 'مولدات عودة درابيه', 'notes' => null, 'is_active' => true],
            ['phone' => '0598785017', 'name' => 'مولدات نور حياتك', 'notes' => null, 'is_active' => true],
            ['phone' => '0598878343', 'name' => 'مولدات اﻷنوار', 'notes' => null, 'is_active' => true],
            ['phone' => '0598882302', 'name' => 'مولدات القيسي', 'notes' => null, 'is_active' => true],
            ['phone' => '0598888381', 'name' => 'مولدات حنيف', 'notes' => null, 'is_active' => true],
            ['phone' => '0598913020', 'name' => 'مولدات الخالدي فلسطين', 'notes' => null, 'is_active' => true],
            ['phone' => '0598926540', 'name' => 'مولدات ثابت', 'notes' => null, 'is_active' => true],
            ['phone' => '0599022456', 'name' => 'مولدات اﻷصدقاء', 'notes' => null, 'is_active' => true],
            ['phone' => '0599060356', 'name' => 'مولدات أبو حصيرة الميناء', 'notes' => null, 'is_active' => true],
            ['phone' => '0599127723', 'name' => 'مولدات سمور الشمال', 'notes' => null, 'is_active' => true],
            ['phone' => '0599145620', 'name' => 'مولدات دادر', 'notes' => null, 'is_active' => true],
            ['phone' => '0599242292', 'name' => 'مولدات غزال 1', 'notes' => null, 'is_active' => true],
            ['phone' => '0599255266', 'name' => 'مولدات نعيم', 'notes' => null, 'is_active' => true],
            ['phone' => '0599266841', 'name' => 'مولدات خربة العدس', 'notes' => null, 'is_active' => true],
            ['phone' => '0599322453', 'name' => 'مولدات اﻹيطالي', 'notes' => null, 'is_active' => true],
            ['phone' => '0599343853', 'name' => 'مولدات اﻷزعر', 'notes' => null, 'is_active' => true],
            ['phone' => '0599349217', 'name' => 'مولدات اللوح', 'notes' => null, 'is_active' => true],
            ['phone' => '0599409239', 'name' => 'مولدات الزنط الشمال', 'notes' => null, 'is_active' => true],
            ['phone' => '0599433833', 'name' => 'مولدات حرز والهمص', 'notes' => null, 'is_active' => true],
            ['phone' => '0599444479', 'name' => 'مولدات هنية', 'notes' => null, 'is_active' => true],
            ['phone' => '0599489316', 'name' => 'مولدات الحمايدة البلد', 'notes' => null, 'is_active' => true],
            ['phone' => '0599505234', 'name' => 'مولدات برشولة بور', 'notes' => null, 'is_active' => true],
            ['phone' => '0599560988', 'name' => 'مولدات البابا الشيخ عجلين', 'notes' => null, 'is_active' => true],
            ['phone' => '0599562127', 'name' => 'مولدات العيون', 'notes' => null, 'is_active' => true],
            ['phone' => '0599564418', 'name' => 'مولدات بكر', 'notes' => null, 'is_active' => true],
            ['phone' => '0599565644', 'name' => 'مولدات اﻹخوة', 'notes' => null, 'is_active' => true],
            ['phone' => '0599601549', 'name' => 'مولدات النور الساطع', 'notes' => null, 'is_active' => true],
            ['phone' => '0599741884', 'name' => 'مولدات القدس', 'notes' => null, 'is_active' => true],
            ['phone' => '0599763405', 'name' => 'مولدات السعادة', 'notes' => null, 'is_active' => true],
            ['phone' => '0599764597', 'name' => 'مولدات نور بيتك', 'notes' => null, 'is_active' => true],
            ['phone' => '0599777344', 'name' => 'مولدات زنون والخالدي
للطاقة البدليلة', 'notes' => null, 'is_active' => true],
            ['phone' => '0599783335', 'name' => 'مولدات اليرموك', 'notes' => null, 'is_active' => true],
            ['phone' => '0599808771', 'name' => 'مولدات السوافيري الزيتون', 'notes' => null, 'is_active' => true],
            ['phone' => '0599885366', 'name' => 'مولدات الشمالي كريم', 'notes' => null, 'is_active' => true],
            ['phone' => '0599915373', 'name' => 'مولدات جولدن باور', 'notes' => null, 'is_active' => true],
            ['phone' => '0599930666', 'name' => 'مولدات زنون والشاعر', 'notes' => null, 'is_active' => true],
            ['phone' => '0599950967', 'name' => 'مولدات بارود', 'notes' => null, 'is_active' => true],
            ['phone' => '0599994484', 'name' => 'مولدات المخابرات', 'notes' => null, 'is_active' => true],
            ['phone' => '0599999977', 'name' => 'مولدات الجرجاوي', 'notes' => null, 'is_active' => true],
        ];

        $this->command->info('بدء إضافة الأرقام المصرح بها...');

        // حذف الأرقام الموجودة إذا طُلب ذلك
        $existingCount = AuthorizedPhone::count();
        if ($existingCount > 0) {
            $this->command->info("تم العثور على {$existingCount} رقم موجود. سيتم حذفها...");
            AuthorizedPhone::truncate();
        }

        $count = 0;
        $duplicates = 0;

        foreach ($phones as $phoneData) {
            // التحقق من عدم التكرار
            if (!AuthorizedPhone::where('phone', $phoneData['phone'])->exists()) {
                AuthorizedPhone::create([
                    'phone' => $phoneData['phone'],
                    'name' => $phoneData['name'] ?? null,
                    'notes' => $phoneData['notes'] ?? null,
                    'is_active' => $phoneData['is_active'] ?? true,
                    'created_by' => 1, // السوبر أدمن
                ]);
                $count++;
            } else {
                $duplicates++;
            }
        }

        $this->command->info("تم إضافة {$count} رقم بنجاح!");
        if ($duplicates > 0) {
            $this->command->warn("تم تخطي {$duplicates} رقم مكرر");
        }
    }
}
