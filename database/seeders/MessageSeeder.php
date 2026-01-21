<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\User;
use App\Models\Operator;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('جاري إنشاء الرسائل...');

        // الحصول على المستخدمين
        $superAdmin = User::where('role', Role::SuperAdmin)->first();
        $admin = User::where('role', Role::Admin)->first();
        $companyOwners = User::where('role', Role::CompanyOwner)->get();
        $employees = User::whereIn('role', [Role::Employee, Role::Technician])->get();
        
        if (!$superAdmin) {
            $this->command->error('لم يتم العثور على Super Admin! يرجى تشغيل UserSeeder أولاً.');
            return;
        }

        $operators = Operator::all();
        
        if ($operators->isEmpty()) {
            $this->command->error('لم يتم العثور على مشغلين! يرجى تشغيل OperatorsWithDataSeeder أولاً.');
            return;
        }

        $messages = [];

        // 1. رسائل من Super Admin إلى جميع المشغلين
        if ($operators->count() > 0) {
            $messages[] = [
                'sender_id' => $superAdmin->id,
                'receiver_id' => null,
                'operator_id' => null,
                'subject' => 'ترحيب بجميع المشغلين في النظام',
                'body' => 'مرحباً بكم جميعاً في منصة نور لإدارة وحدات التوليد. نتمنى لكم تجربة ممتعة وفعالة في استخدام النظام.',
                'type' => 'admin_to_all',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ];

            $messages[] = [
                'sender_id' => $superAdmin->id,
                'receiver_id' => null,
                'operator_id' => null,
                'subject' => 'تحديثات جديدة على النظام',
                'body' => 'نود إعلامكم بأنه تم إضافة ميزات جديدة على النظام لتحسين تجربة الاستخدام. يرجى مراجعة الدليل الإرشادي.',
                'type' => 'admin_to_all',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ];
        }

        // 2. رسائل من Admin إلى مشغلين محددين
        if ($admin && $operators->count() > 0) {
            $firstOperator = $operators->first();
            $messages[] = [
                'sender_id' => $admin->id,
                'receiver_id' => null,
                'operator_id' => $firstOperator->id,
                'subject' => 'طلب تحديث بيانات المشغل',
                'body' => 'نود منكم تحديث بيانات المشغل في أقرب وقت ممكن لضمان دقة المعلومات في النظام.',
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ];
        }

        // 3. رسائل بين المشغلين
        if ($companyOwners->count() >= 2) {
            $operator1 = $companyOwners->first();
            $operator2 = $companyOwners->skip(1)->first();
            $operator1Operator = $operator1->ownedOperators()->first();
            $operator2Operator = $operator2->ownedOperators()->first();

            if ($operator1Operator && $operator2Operator) {
                $messages[] = [
                    'sender_id' => $operator1->id,
                    'receiver_id' => $operator2->id,
                    'operator_id' => null,
                    'subject' => 'طلب تنسيق مشترك',
                    'body' => 'نود التنسيق معكم بخصوص مشروع مشترك في مجال التوليد. هل يمكننا التواصل؟',
                    'type' => 'operator_to_operator',
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => Carbon::now()->subDays(4),
                    'updated_at' => Carbon::now()->subDays(4),
                ];

                $messages[] = [
                    'sender_id' => $operator2->id,
                    'receiver_id' => $operator1->id,
                    'operator_id' => null,
                    'subject' => 'رد: طلب تنسيق مشترك',
                    'body' => 'شكراً لكم على التواصل. نحن مستعدون للاجتماع ومناقشة التفاصيل في الوقت المناسب.',
                    'type' => 'operator_to_operator',
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(3)->addHours(2),
                    'created_at' => Carbon::now()->subDays(3),
                    'updated_at' => Carbon::now()->subDays(3),
                ];
            }
        }

        // 4. رسائل من المشغلين لموظفيهم
        foreach ($companyOwners as $owner) {
            $operator = $owner->ownedOperators()->first();
            if (!$operator) continue;

            $ownerEmployees = $employees->filter(function($emp) use ($operator) {
                return $emp->operators->contains($operator->id);
            })->take(2);

            if ($ownerEmployees->isNotEmpty()) {
                $messages[] = [
                    'sender_id' => $owner->id,
                    'receiver_id' => null,
                    'operator_id' => $operator->id,
                    'subject' => 'اجتماع أسبوعي مع الفريق',
                    'body' => 'نود دعوتكم لحضور اجتماع أسبوعي لمناقشة التحديثات والتطورات في العمل.',
                    'type' => 'operator_to_staff',
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => Carbon::now()->subDays(1),
                    'updated_at' => Carbon::now()->subDays(1),
                ];
            }

            // رسائل مباشرة من المشغل لموظفين محددين
            if ($ownerEmployees->count() > 0) {
                $firstEmployee = $ownerEmployees->first();
                $messages[] = [
                    'sender_id' => $owner->id,
                    'receiver_id' => $firstEmployee->id,
                    'operator_id' => null,
                    'subject' => 'مهمة جديدة',
                    'body' => 'نود منك متابعة حالة المولدات وإعداد تقرير شهري عن الأداء.',
                    'type' => 'operator_to_operator',
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => Carbon::now()->subHours(5),
                    'updated_at' => Carbon::now()->subHours(5),
                ];
            }
        }

        // 5. رسائل بين الموظفين
        if ($employees->count() >= 2) {
            $emp1 = $employees->first();
            $emp2 = $employees->skip(1)->first();

            $messages[] = [
                'sender_id' => $emp1->id,
                'receiver_id' => $emp2->id,
                'operator_id' => null,
                'subject' => 'استفسار عن عملية صيانة',
                'body' => 'هل يمكنك مساعدتي في فهم إجراءات الصيانة للمولدات من النوع X؟',
                'type' => 'operator_to_operator',
                'is_read' => true,
                'read_at' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subHours(3),
                'updated_at' => Carbon::now()->subHours(3),
            ];
        }

        // 6. رسائل قديمة (مقروءة)
        if ($companyOwners->count() > 0 && $superAdmin) {
            $firstOwner = $companyOwners->first();
            $messages[] = [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $firstOwner->id,
                'operator_id' => null,
                'subject' => 'تأكيد التسجيل في النظام',
                'body' => 'تم تأكيد تسجيل حسابكم في النظام. نتمنى لكم تجربة ممتعة.',
                'type' => 'admin_to_operator',
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(10),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ];
        }

        // 7. إنشاء 3 رسائل افتراضية لكل مستخدم في النظام
        $allUsers = User::where('id', '!=', $superAdmin->id)->get();
        foreach ($allUsers as $user) {
            // الحصول على المشغل المرتبط بالمستخدم (إن وجد)
            $operator = null;
            if ($user->isCompanyOwner()) {
                $operator = $user->ownedOperators()->first();
            } elseif ($user->isEmployee() || $user->isTechnician()) {
                $operator = $user->operators()->first();
            }

            // رسالة ترحيبية
            $messages[] = [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $user->id,
                'operator_id' => $operator?->id,
                'subject' => 'مرحباً بك في منصة نور',
                'body' => "عزيزي/عزيزتي {$user->name}،\n\nنرحب بك في منصة نور لإدارة وحدات التوليد. نتمنى أن تجد في النظام كل ما تحتاجه لإدارة عملك بكفاءة وفعالية.\n\nنتمنى لك تجربة ممتعة!",
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ];

            // رسالة دليل الاستخدام
            $messages[] = [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $user->id,
                'operator_id' => $operator?->id,
                'subject' => 'دليل الاستخدام السريع',
                'body' => "عزيزي/عزيزتي {$user->name}،\n\nيمكنك من خلال النظام:\n- إدارة بيانات المولدات ووحدات التوليد\n- متابعة سجلات التشغيل والوقود\n- إدارة أعمال الصيانة\n- التواصل مع الفريق من خلال نظام الرسائل\n\nللمزيد من المعلومات، يرجى مراجعة الدليل الإرشادي.",
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(6),
                'updated_at' => Carbon::now()->subDays(6),
            ];

            // رسالة معلومات مهمة
            $messages[] = [
                'sender_id' => $superAdmin->id,
                'receiver_id' => $user->id,
                'operator_id' => $operator?->id,
                'subject' => 'معلومات مهمة',
                'body' => "عزيزي/عزيزتي {$user->name}،\n\nنود تذكيرك بأن:\n- يرجى إكمال بيانات المشغل في أقرب وقت ممكن\n- يمكنك التواصل معنا في أي وقت من خلال نظام الرسائل\n- ننصح بتغيير كلمة المرور بعد تسجيل الدخول لأول مرة\n\nنتمنى لك تجربة ناجحة!",
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ];
        }

        // إدراج الرسائل
        foreach ($messages as $message) {
            try {
                Message::create($message);
                $this->command->info("✓ تم إنشاء رسالة: {$message['subject']}");
            } catch (\Exception $e) {
                $this->command->error("✗ فشل إنشاء رسالة: {$message['subject']} - {$e->getMessage()}");
            }
        }

        $this->command->info("تم إنشاء " . count($messages) . " رسالة بنجاح!");
    }
}





