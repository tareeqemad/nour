<?php

namespace App\Http\Controllers;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Helpers\GeneralHelper;
use App\Models\ComplaintSuggestion;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintSuggestionController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للمقترحات والشكاوى
     */
    public function index()
    {
        return view('complaints-suggestions.index');
    }

    /**
     * عرض صفحة إرسال شكوى/مقترح
     */
    public function create()
    {
        // جلب المحافظات من الثوابت (رقم ثابت المحافظات = 1)
        $governorates = ConstantsHelper::get(1);

        return view('complaints-suggestions.create', compact('governorates'));
    }

    /**
     * حفظ شكوى/مقترح جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:complaint,suggestion',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'governorate' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (! Governorate::tryFrom($value)) {
                    $fail('يرجى اختيار محافظة صحيحة');
                }
            }],
            'generator_id' => 'nullable|exists:generators,id',
            'message' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'type.required' => 'يرجى اختيار نوع الطلب',
            'name.required' => 'يرجى إدخال الاسم',
            'phone.required' => 'يرجى إدخال رقم الهاتف',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'governorate.required' => 'يرجى اختيار المحافظة',
            'governorate.integer' => 'يرجى اختيار محافظة صحيحة',
            'generator_id.exists' => 'يرجى اختيار مولد صحيح',
            'message.required' => 'يرجى إدخال الرسالة',
            'message.min' => 'الرسالة يجب أن تكون على الأقل 10 أحرف',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة',
            'image.mimes' => 'نوع الصورة يجب أن يكون: jpeg, png, jpg, gif',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 5 ميجابايت',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints-suggestions', 'public');
        }

        // تحديد المشغل بناءً على generator_id أو المحافظة
        $operatorId = null;
        if ($request->generator_id) {
            // إذا تم اختيار مولد محدد، نأخذ المشغل من المولد
            $generator = Generator::with('generationUnit.operator')->find($request->generator_id);
            if ($generator) {
                // البحث عن المشغل من خلال generation_unit (الطريقة المفضلة)
                if ($generator->generationUnit && $generator->generationUnit->operator) {
                    $operatorId = $generator->generationUnit->operator->id;
                } 
                // Fallback: إذا لم يكن هناك generation_unit، نستخدم operator_id المباشر
                elseif ($generator->operator_id) {
                    $operatorId = $generator->operator_id;
                }
            }
        } else {
            // إذا لم يتم اختيار مولد، نبحث عن المشغلين في المحافظة
            // لكن لا يمكننا تحديد مشغل واحد، لذا سنتركه null
            // وسيتم ربطه لاحقاً من قبل Admin/Energy Authority
        }

        $complaintSuggestion = ComplaintSuggestion::create([
            'type' => $request->type,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'governorate' => Governorate::from($request->governorate),
            'operator_id' => $operatorId,
            'generator_id' => $request->generator_id,
            'subject' => $request->message, // استخدام الرسالة كعنوان
            'message' => $request->message,
            'image' => $imagePath,
            'tracking_code' => ComplaintSuggestion::generateTrackingCode(),
            'status' => 'pending',
            'closed_by_operator' => false,
        ]);

        // إذا كان هناك مشغل محدد، إرسال إشعارات لـ Admin, SuperAdmin, Energy Authority
        if ($operatorId) {
            $operator = \App\Models\Operator::find($operatorId);
            if ($operator) {
                // إرسال إشعارات لـ Admin, SuperAdmin, Energy Authority
                \App\Models\Notification::notifyOperatorApprovers(
                    'complaint_new',
                    'شكوى/مقترح جديد على مشغل',
                    "تم إرسال ".($request->type === 'complaint' ? 'شكوى' : 'مقترح')." جديد على المشغل: {$operator->name} من {$request->name}",
                    route('admin.complaints-suggestions.show', $complaintSuggestion)
                );

                // إرسال إشعار للمشغل
                \App\Models\Notification::notifyOperatorUsers(
                    $operator,
                    'complaint_assigned',
                    'شكوى/مقترح جديد',
                    "تم إرسال ".($request->type === 'complaint' ? 'شكوى' : 'مقترح')." جديد متعلق بمشغلك",
                    route('admin.complaints-suggestions.show', $complaintSuggestion)
                );
            }
        }

        return redirect()->route('complaints-suggestions.track', ['code' => $complaintSuggestion->tracking_code])
            ->with('success', 'تم إرسال '.($request->type === 'complaint' ? 'الشكوى' : 'المقترح').' بنجاح. رمز التتبع: '.$complaintSuggestion->tracking_code);
    }

    /**
     * عرض صفحة متابعة الطلب
     */
    public function track(Request $request)
    {
        $code = trim($request->query('code', ''));
        $complaintSuggestion = null;

        if ($code) {
            // البحث بدون حساسية لحالة الأحرف وtrim
            $complaintSuggestion = ComplaintSuggestion::with('generator')
                ->whereRaw('UPPER(TRIM(tracking_code)) = ?', [strtoupper(trim($code))])
                ->first();
        }

        return view('complaints-suggestions.track', compact('complaintSuggestion', 'code'));
    }

    /**
     * الحصول على المشغلين حسب المحافظة (لصفحة الشكاوى)
     */
    public function getOperatorsByGovernorate(Request $request, int $governorate): JsonResponse
    {
        // التحقق من صحة رقم المحافظة
        if (!Governorate::tryFrom($governorate)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم المحافظة غير صحيح',
            ], 400);
        }

        $activeOnly = $request->boolean('active_only', true);
        
        $operators = GeneralHelper::getOperatorsByGovernorateSimple($governorate, $activeOnly);

        return response()->json([
            'success' => true,
            'data' => $operators->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'city' => $operator->getCityName(),
                    'status' => $operator->status,
                ];
            }),
        ]);
    }

    /**
     * الحصول على المولدات حسب المشغل
     * المولدات مرتبطة بالمشغل عبر وحدات التوليد فقط
     */
    public function getGeneratorsByOperator(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:operators,id',
        ]);

        $operatorId = (int) $request->operator_id;

        // الحصول على وحدات التوليد التابعة للمشغل
        $generationUnitIds = \App\Models\GenerationUnit::where('operator_id', $operatorId)->pluck('id');

        if ($generationUnitIds->isEmpty()) {
            // إذا لم يكن هناك وحدات توليد، إرجاع قائمة فارغة
            return response()->json([]);
        }

        // الحصول على المولدات المرتبطة بوحدات التوليد
        $generators = Generator::whereIn('generation_unit_id', $generationUnitIds)
            ->whereNotNull('generation_unit_id') // التأكد من أن generation_unit_id ليس null
            ->whereHas('statusDetail', function($q) {
                $q->where('code', 'ACTIVE');
            })
            ->select('id', 'name', 'generator_number')
            ->orderBy('name')
            ->get();

        // تنسيق البيانات للعرض
        $formattedGenerators = $generators->map(function ($generator) {
            return [
                'id' => $generator->id,
                'name' => $generator->name . ' (' . $generator->generator_number . ')',
            ];
        });

        return response()->json($formattedGenerators);
    }

    /**
     * الحصول على المولدات حسب المحافظة (للتوافق مع الكود القديم)
     */
    public function getGeneratorsByLocation(Request $request)
    {
        $request->validate([
            'governorate' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (! Governorate::tryFrom((int) $value)) {
                    $fail('يرجى اختيار محافظة صحيحة');
                }
            }],
        ]);

        $governorateValue = (int) $request->governorate;

        // البحث عن المولدات من خلال generation_units باستخدام join مباشرة
        // المولدات مرتبطة بالمشغل عبر وحدات التوليد فقط
        $governorateDetail = \App\Helpers\ConstantsHelper::get(1)
            ->where('value', (string) $governorateValue)
            ->first();
        
        if (!$governorateDetail) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        // المولدات مرتبطة بالمشغل عبر وحدات التوليد فقط
        $generators = Generator::join('generation_units', 'generators.generation_unit_id', '=', 'generation_units.id')
            ->join('operators', 'generation_units.operator_id', '=', 'operators.id')
            ->join('constant_details as status_detail', 'generators.status_id', '=', 'status_detail.id')
            ->where('generation_units.governorate_id', $governorateDetail->id)
            ->where('operators.status', 'active')
            ->where('status_detail.code', 'ACTIVE')
            ->select('generators.id', 'generators.name', 'generators.generator_number', 'operators.name as operator_name')
            ->orderBy('generators.name')
            ->get();

        // تنسيق البيانات للعرض
        $formattedGenerators = $generators->map(function ($generator) {
            return [
                'id' => $generator->id,
                'name' => $generator->name.' ('.$generator->generator_number.') - '.($generator->operator_name ?? 'غير محدد'),
            ];
        });

        return response()->json($formattedGenerators);
    }

    /**
     * البحث عن طلب برمز التتبع
     */
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'يرجى إدخال رمز التتبع',
        ]);

        $code = trim($request->code);
        
        // البحث بدون حساسية لحالة الأحرف
        $complaintSuggestion = ComplaintSuggestion::whereRaw('UPPER(TRIM(tracking_code)) = ?', [strtoupper($code)])->first();

        if (! $complaintSuggestion) {
            return back()->with('error', 'لم يتم العثور على طلب بهذا الرمز. يرجى التحقق من الرمز والمحاولة مرة أخرى.')
                ->withInput(['code' => $code]);
        }

        return redirect()->route('complaints-suggestions.track', ['code' => $code]);
    }
}
