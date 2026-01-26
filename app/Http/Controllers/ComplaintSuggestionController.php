<?php

namespace App\Http\Controllers;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Helpers\GeneralHelper;
use App\Http\Requests\StoreComplaintSuggestionRequest;
use App\Models\ComplaintSuggestion;
use App\Models\Generator;
use App\Services\ComplaintSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintSuggestionController extends Controller
{
    public function __construct(
        private ComplaintSuggestionService $complaintSuggestionService
    ) {}

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
    public function store(StoreComplaintSuggestionRequest $request)
    {
        try {
            $complaintSuggestion = $this->complaintSuggestionService->createComplaint(
                $request->validated(),
                $request->file('image')
            );

            $typeLabel = $request->type === 'complaint' ? 'الشكوى' : 'المقترح';

            return redirect()
                ->route('complaints-suggestions.track', ['code' => $complaintSuggestion->tracking_code])
                ->with('success', "تم إرسال {$typeLabel} بنجاح. رمز التتبع: {$complaintSuggestion->tracking_code}");

        } catch (\Exception $e) {
            \Log::error('Error creating complaint/suggestion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مرة أخرى.');
        }
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
