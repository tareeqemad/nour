<?php

namespace Database\Seeders;

use App\Governorate;
use App\Models\ComplaintSuggestion;
use App\Models\Generator;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSuggestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('بدء إنشاء بيانات الشكاوى والمقترحات...');

        $generators = Generator::all();
        $operators = Operator::all();
        $adminUsers = User::whereIn('role', ['super_admin', 'admin'])->get();

        if ($adminUsers->isEmpty()) {
            $this->command->warn('لا توجد مستخدمين أدمن، سيتم تخطي الردود');
        }

        // محتوى الشكاوى
        $complaintSubjects = [
            'انقطاع الكهرباء المتكرر',
            'صوت عالي من المولد',
            'انقطاع الكهرباء لفترة طويلة',
            'مشكلة في الجهد الكهربائي',
            'رائحة دخان من المولد',
            'عدم استجابة في الوقت المحدد',
            'مشكلة في التوزيع',
            'انقطاع مفاجئ',
            'جهد كهربائي غير مستقر',
            'صيانة غير منتظمة',
        ];

        $complaintMessages = [
            'المولد ينقطع بشكل متكرر مما يسبب إزعاج كبير',
            'صوت المولد عالي جداً ويسبب إزعاج للسكان',
            'انقطع التيار الكهربائي لمدة طويلة ولم يتم حل المشكلة',
            'الجهد الكهربائي غير مستقر ويؤثر على الأجهزة',
            'هناك رائحة دخان قوية تأتي من المولد',
            'المولد لا يعمل في الأوقات المحددة',
            'مشكلة في توزيع الكهرباء على المناطق',
            'انقطاع مفاجئ للتيار الكهربائي',
            'الجهد غير مستقر ويسبب مشاكل في الأجهزة',
            'المولد يحتاج صيانة دورية ولم يتم الالتزام',
        ];

        // محتوى المقترحات
        $suggestionSubjects = [
            'تحسين جدول التشغيل',
            'تركيب نظام إنذار',
            'تحسين الصيانة',
            'زيادة القدرة',
            'تحسين الكفاءة',
            'إضافة مولد احتياطي',
            'تحسين التوزيع',
            'تركيب عداد ذكي',
            'تحسين الخدمة',
            'تدريب الموظفين',
        ];

        $suggestionMessages = [
            'نقترح تحسين جدول التشغيل ليكون أكثر فعالية',
            'نقترح تركيب نظام إنذار للمولدات',
            'نقترح تحسين برنامج الصيانة الدورية',
            'نقترح زيادة قدرة المولد لتغطية الاحتياجات',
            'نقترح تحسين كفاءة المولد لتقليل استهلاك الوقود',
            'نقترح إضافة مولد احتياطي لتجنب الانقطاعات',
            'نقترح تحسين توزيع الكهرباء على المناطق',
            'نقترح تركيب عداد ذكي لمتابعة الاستهلاك',
            'نقترح تحسين جودة الخدمة المقدمة',
            'نقترح تدريب الموظفين على الصيانة الحديثة',
        ];

        $names = [
            'أحمد محمد',
            'محمد خالد',
            'خالد علي',
            'علي حسن',
            'حسن سعيد',
            'سعيد إبراهيم',
            'إبراهيم فتحي',
            'فتحي ناصر',
            'ناصر رامي',
            'رامي وليد',
        ];

        $governorates = [10, 20, 30, 40]; // Gaza, Middle, Khan Yunis, Rafah

        // إنشاء 50 شكوى
        for ($i = 0; $i < 50; $i++) {
            $type = 'complaint';
            $generator = $generators->random();
            $governorate = $governorates[array_rand($governorates)];
            $status = ['pending', 'in_progress', 'resolved', 'rejected'][rand(0, 3)];
            $hasResponse = in_array($status, ['resolved', 'rejected']) && $adminUsers->isNotEmpty();

            $complaint = ComplaintSuggestion::create([
                'type' => $type,
                'name' => $names[rand(0, count($names) - 1)],
                'phone' => '059' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => 'user' . $i . '@example.com',
                'governorate' => $governorate,
                'generator_id' => $generator->id,
                'subject' => $complaintSubjects[rand(0, count($complaintSubjects) - 1)],
                'message' => $complaintMessages[rand(0, count($complaintMessages) - 1)],
                'status' => $status,
                'tracking_code' => ComplaintSuggestion::generateTrackingCode(),
            ]);

            if ($hasResponse) {
                $complaint->update([
                    'response' => 'شكراً لتواصلكم. تمت معالجة الشكوى واتخاذ الإجراءات اللازمة.',
                    'responded_by' => $adminUsers->random()->id,
                    'responded_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        // إنشاء 30 مقترح
        for ($i = 0; $i < 30; $i++) {
            $type = 'suggestion';
            $generator = rand(0, 1) === 1 ? $generators->random() : null; // قد لا يكون مرتبط بمولد
            $governorate = $governorates[array_rand($governorates)];
            $status = ['pending', 'in_progress', 'resolved'][rand(0, 2)];
            $hasResponse = $status === 'resolved' && $adminUsers->isNotEmpty();

            $suggestion = ComplaintSuggestion::create([
                'type' => $type,
                'name' => $names[rand(0, count($names) - 1)],
                'phone' => '059' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => 'suggestion' . $i . '@example.com',
                'governorate' => $generator ? null : $governorate, // إذا لم يكن مرتبط بمولد، نضيف المحافظة
                'generator_id' => $generator?->id,
                'subject' => $suggestionSubjects[rand(0, count($suggestionSubjects) - 1)],
                'message' => $suggestionMessages[rand(0, count($suggestionMessages) - 1)],
                'status' => $status,
                'tracking_code' => ComplaintSuggestion::generateTrackingCode(),
            ]);

            if ($hasResponse) {
                $suggestion->update([
                    'response' => 'شكراً لاقتراحك القيم. سنأخذ اقتراحك بعين الاعتبار ونعمل على دراسته.',
                    'responded_by' => $adminUsers->random()->id,
                    'responded_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $total = ComplaintSuggestion::count();
        $this->command->info("✓ تم إنشاء $total شكوى ومقترح بنجاح");
    }
}



