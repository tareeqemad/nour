<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGeneratorRequest;
use App\Http\Requests\Admin\UpdateGeneratorRequest;
use App\Models\Generator;
use App\Models\Notification;
use App\Models\Operator;
use App\Models\Task;
use App\Models\ComplaintSuggestion;
use App\Models\FuelConsumptionSummary;
use App\Helpers\ConstantsHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GeneratorController extends Controller
{
    /**
     * Display paginated generators list with search and status filters.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Generator::class);

        $user = auth()->user();
        $query = Generator::with([
            'operator',
            'generationUnit',
            'statusDetail',
            'engineTypeDetail',
            'injectionSystemDetail',
            'measurementIndicatorDetail',
            'technicalConditionDetail',
            'controlPanelTypeDetail',
            'controlPanelStatusDetail'
        ]);

        // تحديد المشغل بناءً على دور المستخدم
        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $query->where('operator_id', $currentOperator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $query->whereIn('operator_id', $operators->pluck('id'));
                $currentOperator = $operators->first();
            }
        }

        // فلترة حسب المشغل (للأدوار التي يمكنها اختيار المشغل)
        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority() || $user->isCivilDefense();
        if ($canSelectOperator) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->where('operator_id', $operatorId);
            }
        }

        // فلترة حسب وحدة التوليد
        $generationUnitId = (int) $request->input('generation_unit_id', 0);
        if ($generationUnitId > 0) {
            $query->where('generation_unit_id', $generationUnitId);
        }

        // فلترة حسب المولد
        $generatorId = (int) $request->input('generator_id', 0);
        if ($generatorId > 0) {
            $query->where('id', $generatorId);
        }

        // فلترة حسب الحالة (active/inactive)
        $status = $request->input('status', '');
        if ($status === 'active') {
            $activeStatus = ConstantsHelper::get(3)->firstWhere('code', 'ACTIVE');
            if ($activeStatus) {
                $query->where('status_id', $activeStatus->id);
            }
        } elseif ($status === 'inactive') {
            $inactiveStatus = ConstantsHelper::get(3)->firstWhere('code', 'INACTIVE');
            if ($inactiveStatus) {
                $query->where('status_id', $inactiveStatus->id);
            }
        }

        $generators = $query->latest()->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.generators.partials.list', compact('generators'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $generators->total(),
            ]);
        }

        // جلب المشغلين للفلترة
        $operators = collect();
        if ($canSelectOperator) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        // جلب وحدات التوليد للمشغل الحالي (للمشغل والموظفين)
        $generationUnitsList = collect();
        if ($currentOperator) {
            $generationUnitsList = $currentOperator->generationUnits()
                ->select('id', 'name', 'unit_code', 'status_id')
                ->orderBy('name')
                ->get();
        }

        // جلب ثوابت الحالة للفلترة
        $statusConstants = ConstantsHelper::get(3); // حالة المولد

        return view('admin.generators.index', compact('generators', 'operators', 'statusConstants', 'currentOperator', 'generationUnitsList', 'canSelectOperator'));
    }

    /**
     * Show form for creating a new generator with validation checks.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Generator::class);

        $user = auth()->user();
        $operators = collect();
        $operator = null;

        // تحديد المشغل بناءً على دور المستخدم
        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }
            $operators = $user->ownedOperators;
        } elseif ($user->hasPermission('generators.create')) {
            // المستخدم التابع لمشغل مع صلاحية generators.create
            // التحقق من أن المستخدم تابع لمشغل
            if ($user->operators()->exists()) {
                $operator = $user->operators()->first();
            } elseif ($user->ownedOperators()->exists()) {
                $operator = $user->ownedOperators()->first();
            } else {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }
        } elseif ($user->isTechnician()) {
            // Technician يجب أن يكون تابع لمشغل
            if ($user->operators()->exists()) {
                $operator = $user->operators()->first();
            } else {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. يرجى التواصل مع مدير النظام.');
            }
        } else {
            return redirect()->route('admin.dashboard')
                ->with('error', 'ليس لديك صلاحية لإضافة مولدات.');
        }

        // التحقق من وجود وحدات التوليد
        if ($operator) {
            $generationUnits = $operator->generationUnits;
            if ($generationUnits->isEmpty()) {
                return redirect()->route('admin.generation-units.create')
                    ->with('warning', 'يجب إضافة وحدة توليد على الأقل قبل إضافة المولدات.');
            }

            // إذا تم تحديد generation_unit_id في الطلب، التحقق من أن الوحدة موجودة ومتاحة
            $generationUnitId = $request->input('generation_unit_id');
            if ($generationUnitId) {
                $generationUnit = $generationUnits->find($generationUnitId);
                if (!$generationUnit) {
                    return redirect()->route('admin.generators.index')
                        ->with('error', 'وحدة التوليد المحددة غير موجودة أو غير متاحة.');
                }

                // التحقق من أن عدد المولدات لم يتجاوز العدد المطلوب
                $currentCount = $generationUnit->generators()->count();
                $maxCount = $generationUnit->generators_count;

                if ($currentCount >= $maxCount) {
                    return redirect()->route('admin.generators.index')
                        ->with('error', "لقد وصلت إلى الحد الأقصى لعدد المولدات في هذه الوحدة ({$maxCount}). يمكنك إضافة مولدات جديدة بعد تحديث عدد المولدات في وحدة التوليد.");
                }
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(3), // حالة المولد
            'engine_type' => ConstantsHelper::get(4), // نوع المحرك
            'injection_system' => ConstantsHelper::get(5), // نظام الحقن
            'measurement_indicator' => ConstantsHelper::get(6), // مؤشر القياس
            'technical_condition' => ConstantsHelper::get(7), // الحالة الفنية
            'control_panel_type' => ConstantsHelper::get(8), // نوع لوحة التحكم
            'control_panel_status' => ConstantsHelper::get(9), // حالة لوحة التحكم
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        return view('admin.generators.create', compact('operators', 'constants'));
    }

    /**
     * Store newly created generator with images and fuel tanks data.
     */
    public function store(StoreGeneratorRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Generator::class);

        $user = auth()->user();

        $data = $request->validated();

        if ($request->hasFile('engine_data_plate_image')) {
            $data['engine_data_plate_image'] = $request->file('engine_data_plate_image')->store('generators/engine-plates', 'public');
        }
        if ($request->hasFile('generator_data_plate_image')) {
            $data['generator_data_plate_image'] = $request->file('generator_data_plate_image')->store('generators/generator-plates', 'public');
        }
        if ($request->hasFile('control_panel_image')) {
            $data['control_panel_image'] = $request->file('control_panel_image')->store('generators/control-panels', 'public');
        }

        // تحديد المشغل من المستخدم مباشرة (للسلامة)
        $operator = null;
        $authUser = auth()->user();
        
        if ($authUser->isSuperAdmin()) {
            // SuperAdmin يمكنه اختيار أي مشغل من الـ form
            if (!empty($data['operator_id'])) {
                $operator = \App\Models\Operator::find($data['operator_id']);
                if (!$operator) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'المشغل المحدد غير موجود.');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'يجب اختيار المشغل.');
            }
        } elseif ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        } elseif ($authUser->hasPermission('generators.create') || $authUser->isTechnician()) {
            // المستخدم التابع لمشغل (Employee أو Technician)
            if ($authUser->operators()->exists()) {
                $operator = $authUser->operators()->first();
            } elseif ($authUser->ownedOperators()->exists()) {
                $operator = $authUser->ownedOperators()->first();
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'ليس لديك صلاحية لإضافة مولدات.');
        }

        // تعيين operator_id من المستخدم (للسلامة)
        $data['operator_id'] = $operator->id;

        // التحقق من وجود generation_unit_id
        if (empty($data['generation_unit_id'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'يجب اختيار وحدة التوليد.');
        }

        // التحقق من أن عدد المولدات لم يتجاوز العدد المطلوب
        $generationUnit = \App\Models\GenerationUnit::find($data['generation_unit_id']);
        if (!$generationUnit) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'وحدة التوليد المحددة غير موجودة.');
        }

        // التحقق من أن وحدة التوليد تنتمي للمشغل الصحيح (للسلامة)
        if ($generationUnit->operator_id !== $operator->id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'وحدة التوليد المحددة لا تنتمي للمشغل الخاص بك.');
        }

        $currentCount = $generationUnit->generators()->count();
        $maxCount = $generationUnit->generators_count;
        if ($maxCount > 0 && $currentCount >= $maxCount) {
            return redirect()->back()
                ->withInput()
                ->with('error', "لقد وصلت إلى الحد الأقصى لعدد المولدات في هذه الوحدة ({$maxCount}).");
        }

        // التحقق من أن مجموع قدرات المولدات لا يتجاوز إجمالي القدرة لوحدة التوليد
        if ($generationUnit->total_capacity && isset($data['capacity_kva']) && $data['capacity_kva'] > 0) {
            // حساب مجموع قدرات المولدات الحالية
            $currentTotalCapacity = $generationUnit->generators()->sum('capacity_kva') ?? 0;
            // إضافة القدرة الجديدة
            $newTotalCapacity = $currentTotalCapacity + $data['capacity_kva'];
            
            if ($newTotalCapacity > $generationUnit->total_capacity) {
                $remaining = $generationUnit->total_capacity - $currentTotalCapacity;
                return redirect()->back()
                    ->withInput()
                    ->with('error', "مجموع قدرات المولدات ({$newTotalCapacity} KVA) يتجاوز إجمالي القدرة لوحدة التوليد ({$generationUnit->total_capacity} KVA). القدرة المتبقية المتاحة: {$remaining} KVA.");
            }
        }

        // operator_id تم تعيينه مسبقاً من المستخدم (للسلامة)

        // توليد رقم المولد تلقائياً إذا لم يكن محدداً
        if (empty($data['generator_number'])) {
            $data['generator_number'] = Generator::getNextGeneratorNumber($data['generation_unit_id']);
            if (!$data['generator_number']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'تعذر توليد رقم المولد. تأكد من أن وحدة التوليد لديها unit_code وأن عدد المولدات لم يتجاوز 99.');
            }
        }

        $generator = Generator::create($data);

        $generator->load('operator');
        if ($generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'generator_added',
                'تم إضافة مولد جديد',
                "تم إضافة المولد: {$generator->name}",
                route('admin.generators.show', $generator)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم إنشاء المولد بنجاح.');
    }

    /**
     * Display detailed information about the specified generator.
     */
    /**
     * Display QR Code for generator.
     */
    public function qrCode(Generator $generator): View
    {
        $this->authorize('view', $generator);

        $generator->load(['operator', 'generationUnit']);

        // إنشاء بيانات QR Code - استخدام URL يفتح معلومات المولد
        $qrData = route('qr.generator', ['code' => $generator->generator_number ?? 'GEN-' . $generator->id]);
        
        // مسار حفظ QR Code
        $qrCodePath = 'qr-codes/generators/' . $generator->id . '.svg';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // التحقق من وجود QR Code محفوظ
        if (!file_exists($fullPath) || !$generator->qr_code_generated_at) {
            // إنشاء مجلد إذا لم يكن موجوداً
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // إنشاء QR Code
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrData);

            // حفظ QR Code
            file_put_contents($fullPath, $qrCodeSvg);

            // تسجيل تاريخ توليد QR Code
            $generator->update(['qr_code_generated_at' => now()]);
        } else {
            // قراءة QR Code المحفوظ
            $qrCodeSvg = file_get_contents($fullPath);
        }

        // بيانات إضافية للعرض في الصفحة
        $qrInfo = [
            'type' => 'generator',
            'id' => $generator->id,
            'generator_number' => $generator->generator_number,
            'name' => $generator->name,
            'operator_id' => $generator->operator_id,
            'operator_name' => $generator->operator?->name,
            'generation_unit_id' => $generator->generation_unit_id,
            'generation_unit_code' => $generator->generationUnit?->unit_code,
        ];

        return view('admin.generators.qr-code', compact('generator', 'qrCodeSvg', 'qrInfo'));
    }

    public function show(Generator $generator): View
    {
        $this->authorize('view', $generator);

        $generator->load([
            'operator', 
            'generationUnit.fuelTanks',
            'statusDetail',
            'engineTypeDetail',
            'injectionSystemDetail',
            'measurementIndicatorDetail',
            'technicalConditionDetail',
            'controlPanelTypeDetail',
            'controlPanelStatusDetail'
        ]);

        return view('admin.generators.show', compact('generator'));
    }

    /**
     * Show form for editing the specified generator record.
     */
    public function edit(Generator $generator): View
    {
        $this->authorize('update', $generator);

        $generator->load('generationUnit');

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::all();
        } elseif ($user->isCompanyOwner()) {
            $operators = $user->ownedOperators;
        } elseif ($user->isTechnician()) {
            $operators = $user->operators;
        }

        $constants = [
            'status' => ConstantsHelper::get(3), // حالة المولد
            'engine_type' => ConstantsHelper::get(4), // نوع المحرك
            'injection_system' => ConstantsHelper::get(5), // نظام الحقن
            'measurement_indicator' => ConstantsHelper::get(6), // مؤشر القياس
            'technical_condition' => ConstantsHelper::get(7), // الحالة الفنية
            'control_panel_type' => ConstantsHelper::get(8), // نوع لوحة التحكم
            'control_panel_status' => ConstantsHelper::get(9), // حالة لوحة التحكم
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
        ];

        return view('admin.generators.edit', compact('generator', 'operators', 'constants'));
    }

    /**
     * Update generator data including images and fuel tanks information.
     */
    public function update(UpdateGeneratorRequest $request, Generator $generator): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $generator);

        $data = $request->validated();

        // تحديد المشغل من المستخدم مباشرة (للسلامة)
        $operator = null;
        $authUser = auth()->user();
        
        if ($authUser->isSuperAdmin()) {
            // SuperAdmin يمكنه اختيار أي مشغل من الـ form
            if (!empty($data['operator_id'])) {
                $operator = \App\Models\Operator::find($data['operator_id']);
                if (!$operator) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'المشغل المحدد غير موجود.');
                }
            } else {
                // إذا لم يتم تحديد مشغل، استخدام المشغل الحالي للمولد
                $operator = $generator->operator;
            }
        } elseif ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
            // التحقق من أن المولد ينتمي للمشغل
            if ($generator->operator_id !== $operator->id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'ليس لديك صلاحية لتعديل هذا المولد.');
            }
        } elseif ($authUser->hasPermission('generators.update') || $authUser->isTechnician()) {
            // المستخدم التابع لمشغل (Employee أو Technician)
            if ($authUser->operators()->exists()) {
                $operator = $authUser->operators()->first();
            } elseif ($authUser->ownedOperators()->exists()) {
                $operator = $authUser->ownedOperators()->first();
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
            // التحقق من أن المولد ينتمي للمشغل
            if ($generator->operator_id !== $operator->id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'ليس لديك صلاحية لتعديل هذا المولد.');
            }
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'ليس لديك صلاحية لتعديل المولدات.');
        }

        // تعيين operator_id من المستخدم (للسلامة)
        $data['operator_id'] = $operator->id;

        // إذا تم تغيير generation_unit_id، التحقق من أنه ينتمي للمشغل الصحيح
        if (isset($data['generation_unit_id']) && $data['generation_unit_id'] != $generator->generation_unit_id) {
            $newGenerationUnit = \App\Models\GenerationUnit::find($data['generation_unit_id']);
            if (!$newGenerationUnit) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'وحدة التوليد المحددة غير موجودة.');
            }
            // التحقق من أن وحدة التوليد الجديدة تنتمي للمشغل الصحيح
            if ($newGenerationUnit->operator_id !== $operator->id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'وحدة التوليد المحددة لا تنتمي للمشغل الخاص بك.');
            }
        }

        if ($request->hasFile('engine_data_plate_image')) {
            if ($generator->engine_data_plate_image) {
                Storage::disk('public')->delete($generator->engine_data_plate_image);
            }
            $data['engine_data_plate_image'] = $request->file('engine_data_plate_image')->store('generators/engine-plates', 'public');
        }
        if ($request->hasFile('generator_data_plate_image')) {
            if ($generator->generator_data_plate_image) {
                Storage::disk('public')->delete($generator->generator_data_plate_image);
            }
            $data['generator_data_plate_image'] = $request->file('generator_data_plate_image')->store('generators/generator-plates', 'public');
        }
        if ($request->hasFile('control_panel_image')) {
            if ($generator->control_panel_image) {
                Storage::disk('public')->delete($generator->control_panel_image);
            }
            $data['control_panel_image'] = $request->file('control_panel_image')->store('generators/control-panels', 'public');
        }

        // التحقق من أن مجموع قدرات المولدات لا يتجاوز إجمالي القدرة لوحدة التوليد
        $generationUnit = isset($data['generation_unit_id']) 
            ? \App\Models\GenerationUnit::find($data['generation_unit_id'])
            : $generator->generationUnit;
        if ($generationUnit && $generationUnit->total_capacity && isset($data['capacity_kva']) && $data['capacity_kva'] > 0) {
            // حساب مجموع قدرات المولدات الحالية (باستثناء المولد الحالي)
            $currentTotalCapacity = $generationUnit->generators()
                ->where('id', '!=', $generator->id)
                ->sum('capacity_kva') ?? 0;
            // إضافة القدرة الجديدة
            $newTotalCapacity = $currentTotalCapacity + $data['capacity_kva'];
            
            if ($newTotalCapacity > $generationUnit->total_capacity) {
                $remaining = $generationUnit->total_capacity - $currentTotalCapacity;
                return redirect()->back()
                    ->withInput()
                    ->with('error', "مجموع قدرات المولدات ({$newTotalCapacity} KVA) يتجاوز إجمالي القدرة لوحدة التوليد ({$generationUnit->total_capacity} KVA). القدرة المتبقية المتاحة: {$remaining} KVA.");
            }
        }

        $generator->update($data);

        $generator->load('operator');
        if ($generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'generator_updated',
                'تم تحديث مولد',
                "تم تحديث بيانات المولد: {$generator->name}",
                route('admin.generators.show', $generator)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم تحديث بيانات المولد بنجاح.');
    }

    /**
     * Delete generator record and associated image files from storage.
     */
    public function destroy(Request $request, Generator $generator): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $generator);

        // التحقق من وجود سجلات مرتبطة تمنع الحذف
        $deletionCheck = $generator->canBeDeleted();

        if (!$deletionCheck['can_delete']) {
            $relatedRecordsLabels = array_map(function($record) {
                return $record['label'] . ' (' . $record['count'] . ')';
            }, $deletionCheck['related_records']);
            
            $message = 'لا يمكن حذف المولد لأنه يحتوي على سجلات مرتبطة: ' . implode('، ', $relatedRecordsLabels) . '. يرجى حذف أو نقل هذه السجلات أولاً.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()
                ->with('error', $message);
        }

        // حذف الصور المرتبطة
        if ($generator->engine_data_plate_image) {
            Storage::disk('public')->delete($generator->engine_data_plate_image);
        }
        if ($generator->generator_data_plate_image) {
            Storage::disk('public')->delete($generator->generator_data_plate_image);
        }
        if ($generator->control_panel_image) {
            Storage::disk('public')->delete($generator->control_panel_image);
        }

        $generatorName = $generator->name;
        $generator->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المولد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generators.index')
            ->with('success', 'تم حذف المولد بنجاح.');
    }

    /**
     * التحقق من إمكانية حذف المولد (API)
     */
    public function canDelete(Request $request, Generator $generator): JsonResponse
    {
        $this->authorize('delete', $generator);
        
        $deletionCheck = $generator->canBeDeleted();
        
        return response()->json([
            'success' => true,
            'data' => $deletionCheck,
        ]);
    }

    /**
     * Get generation units for a specific operator (AJAX).
     * This method allows access even if operator is not approved, as generators
     * should be accessible regardless of approval status.
     */
    public function getGenerationUnits(Operator $operator): JsonResponse
    {
        $user = auth()->user();

        // Allow SuperAdmin and Admin to access any operator
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Continue to fetch generation units
        }
        // Allow CompanyOwner to access their own operator, even if not approved
        elseif ($user->isCompanyOwner() && $user->ownsOperator($operator)) {
            // Continue to fetch generation units
        }
        // Allow Employee and Technician to access operators they belong to
        elseif (($user->isEmployee() || $user->isTechnician()) && $user->belongsToOperator($operator)) {
            // Continue to fetch generation units
        }
        // Otherwise, check standard authorization
        elseif (!$user->can('view', $operator)) {
            abort(403, 'غير مصرح لك بالوصول إلى بيانات هذا المشغل.');
        }

        $generationUnits = $operator->generationUnits()
            ->select('id', 'name', 'unit_code', 'generators_count')
            ->get()
            ->map(function ($unit) {
                $currentCount = $unit->generators()->count();
                $maxCount = $unit->generators_count;
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'unit_code' => $unit->unit_code,
                    'label' => "{$unit->name} ({$unit->unit_code}) - {$currentCount}/{$maxCount} مولد",
                    'current_count' => $currentCount,
                    'max_count' => $maxCount,
                    'available' => $currentCount < $maxCount,
                ];
            });

        return response()->json([
            'success' => true,
            'generation_units' => $generationUnits,
        ]);
    }

    /**
     * Generate generator number for a specific generation unit (AJAX).
     */
    public function generateGeneratorNumber(\App\Models\GenerationUnit $generationUnit): JsonResponse
    {
        $this->authorize('view', $generationUnit);

        // التحقق من أن عدد المولدات لم يتجاوز العدد المطلوب
        $currentCount = $generationUnit->generators()->count();
        $maxCount = $generationUnit->generators_count;
        
        if ($currentCount >= $maxCount) {
            return response()->json([
                'success' => false,
                'message' => "لقد وصلت إلى الحد الأقصى لعدد المولدات في هذه الوحدة ({$maxCount}).",
            ], 400);
        }

        $generatorNumber = Generator::getNextGeneratorNumber($generationUnit->id);
        
        if (!$generatorNumber) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر توليد رقم المولد. تأكد من أن وحدة التوليد لديها unit_code وأن عدد المولدات لم يتجاوز 99.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'generator_number' => $generatorNumber,
        ]);
    }

    /**
     * Get generators for a specific generation unit (AJAX).
     */
    public function getGeneratorsByGenerationUnit(Request $request, int $generationUnitId): JsonResponse
    {
        $user = auth()->user();
        
        // التحقق من الصلاحيات
        $generationUnit = \App\Models\GenerationUnit::find($generationUnitId);
        if (!$generationUnit) {
            return response()->json([
                'success' => false,
                'message' => 'وحدة التوليد غير موجودة',
                'generators' => []
            ], 404);
        }

        // للمشغل والموظفين: التحقق من أن وحدة التوليد مرتبطة بهم
        if ($user->isCompanyOwner()) {
            $userOperator = $user->ownedOperators()->first();
            if (!$userOperator) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد مشغل مرتبط بحسابك',
                    'generators' => []
                ], 403);
            }
            if ($generationUnit->operator_id != $userOperator->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول لهذه وحدة التوليد',
                    'generators' => []
                ], 403);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->toArray();
            if (empty($userOperatorIds) || !in_array($generationUnit->operator_id, $userOperatorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول لهذه وحدة التوليد',
                    'generators' => []
                ], 403);
            }
        }
        // للـ SuperAdmin و Admin و EnergyAuthority و CivilDefense: السماح بالوصول بدون قيود

        // فلترة حسب الحالة (إذا كانت محددة)
        $status = trim((string) $request->input('status', ''));
        
        $query = Generator::where('generation_unit_id', $generationUnit->id);
        
        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $statusConstant = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 3);
            })->where('code', strtoupper($status) === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE')->first();
            
            if ($statusConstant) {
                $query->where('status_id', $statusConstant->id);
            }
        }

        $generators = $query->select('id', 'name', 'generator_number', 'status_id')
            ->with('statusDetail')
            ->orderBy('generator_number')
            ->get();

        return response()->json([
            'success' => true,
            'generators' => $generators->map(function($generator) {
                return [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number,
                    'status_id' => $generator->status_id,
                    'status_label' => $generator->statusDetail?->label ?? 'غير محدد',
                    'status_code' => $generator->statusDetail?->code ?? null,
                ];
            })
        ]);
    }
}
