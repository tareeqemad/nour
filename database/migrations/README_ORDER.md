# تسلسل الـ Migrations (الاعتماديات)

Laravel يشغّل الـ migrations حسب ترتيب **اسم الملف** (ASC)، وعند الـ rollback يعكس الترتيب (آخر ملف يُشغّل أولاً يُتراجع عنه).

حتى يعمل `migrate:refresh` و `migrate:rollback` بدون أخطاء مفاتيح أجنبية، **أي جدول يعتمد على جدول آخر يجب أن يكون اسم migration إنشائه لاحقاً** (تاريخ/وقت أكبر) حتى يُحذف أولاً عند الـ rollback.

## الترتيب الحالي (من الأب للابن)

1. **0001_01_01_000000** – users, password_reset_tokens, sessions  
   (لا يعتمد على جداول أخرى في نفس المشروع)

2. **2025_12_23_090000** – constant_masters  

3. **2025_12_23_090100** – constant_details (يعتمد: constant_masters)  

4. **2025_12_23_090150** – roles  

5. **2025_12_23_092100** – **operators** (يعتمد: users)  

6. **2025_12_23_092101** – generators (يعتمد: operators, constant_details, users)  

7. **2025_12_23_092112** – operator_user (يعتمد: operators, users)  

8. **2025_12_23_133038** – fuel_tanks (يعتمد: generation_units لاحقاً)  

9. **2025_12_23_134438** – operation_logs (يعتمد: operators)  

10. **2025_12_23_134440** – fuel_efficiencies (يعتمد: generators، ثم يُضاف operator_id لاحقاً)  

11. **2025_12_23_134441** – maintenance_records (يعتمد: generators)  

12. **2025_12_24_000001** – **generation_units** (يعتمد: operators, constant_details, users)  

13. **2025_12_24_000003** – add generation_unit_id to generators  

14. **2025_12_24_134443** – **compliance_safeties** (يعتمد: operators, generation_units, generators, users, constant_details)  

15. باقي الـ migrations (roles, permissions, messages, tasks, …) كلها بأوقات لاحقة وتعتمد على الجداول أعلاه.

## قاعدة عند إضافة migration جديد

- أي جدول له `foreignId('operator_id')->constrained('operators')` يجب أن يكون **تاريخ/وقت ملف الـ migration أكبر من** `2025_12_23_092100`.
- أي جدول يعتمد على `generation_units` أو `generators` يجب أن يكون تاريخه **بعد** `2025_12_24_000001` و `2025_12_23_092101` على التوالي.
- في الـ **down()**: احذف أولاً الجداول أو الأعمدة التي تعتمد على الجدول الحالي إن وُجدت، أو احذف الـ FK قبل حذف الجدول عند الحاجة (مثل constant_details و parent_detail_id).

## ملاحظة عن users

في `0001_01_01_000000_create_users_table` الـ down() يحذف بهذا الترتيب: **sessions** ثم **password_reset_tokens** ثم **users** (لأن sessions تعتمد على user_id).
