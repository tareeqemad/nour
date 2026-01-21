<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGenerationUnitRequest;
use App\Http\Requests\Admin\UpdateGenerationUnitRequest;
use App\Models\FuelTank;
use App\Models\FuelEfficiency;
use App\Models\GenerationUnit;
use App\Models\Operator;
use App\Models\OperatorTerritory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\AuditLog;

class GenerationUnitController extends Controller
{
    /**
     * Display a listing of generation units.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', GenerationUnit::class);

        $user = auth()->user();
        $query = GenerationUnit::with(['operator', 'generators', 'statusDetail', 'operationEntityDetail', 'synchronizationAvailableDetail', 'environmentalComplianceStatusDetail'])->withCount('generators as actual_generators_count');

        // فلترة حسب نوع المستخدم
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operatorIds = $user->operators()->pluck('operators.id');
            $query->whereIn('operator_id', $operatorIds);
        }

        // فلترة حسب الحالة (يمكن استخدام status أو status_id)
        $status = trim((string) $request->input('status', ''));
        $statusId = (int) $request->input('status_id', 0);
        
        if ($statusId > 0) {
            // استخدام status_id مباشرة (إذا أُرسل)
            $query->where('status_id', $statusId);
        } elseif ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            // استخدام status (active/inactive) - البحث عن status_id المناسب
            // constant_number = 15 لحالة الوحدة
            $statusConstant = \App\Models\ConstantDetail::whereHas('master', function($q) {
                    $q->where('constant_number', 15); // حالة الوحدة
                })
                ->where('code', strtoupper($status) === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE')
                ->first();
            
            if ($statusConstant) {
                $query->where('status_id', $statusConstant->id);
            }
        }

        // فلترة حسب المشغل
        $operatorId = 0;
        
        // للمشغل والموظفين: المشغل تلقائي (لا يمكن تعديله) - تجاهل operator_id من الطلب
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operatorId = $operator->id;
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            // للموظفين: أول مشغل مرتبط بهم
            $firstOperator = $user->operators()->first();
            if ($firstOperator) {
                $operatorId = $firstOperator->id;
            }
        } elseif ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority() || $user->isCivilDefense()) {
            // للأدوار الأخرى: يمكن اختيار المشغل من الطلب
            $operatorId = (int) $request->input('operator_id', 0);
        }
        
        // فلترة حسب المشغل المحدد
        if ($operatorId > 0) {
            $query->where('operator_id', $operatorId);
            
            // فلترة بوحدة التوليد (إذا كانت محددة)
            $generationUnitId = (int) $request->input('generation_unit_id', 0);
            if ($generationUnitId > 0) {
                $query->where('id', $generationUnitId);
            }
        }

        $generationUnits = $query->latest()->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.generation-units.partials.list', compact('generationUnits'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $generationUnits->total(),
            ]);
        }

        // جلب المشغلين للفلاتر
        $operators = collect();
        $currentOperator = null;
        $generationUnitsList = collect();
        
        // للمشغل والموظفين: المشغل تلقائي
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $currentOperator = $user->operators()->first();
        } elseif ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority() || $user->isCivilDefense()) {
            // للأدوار الأخرى: جلب جميع المشغلين
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
            
            // إذا تم اختيار مشغل، جلب وحدات التوليد الخاصة به
            $selectedOperatorId = (int) $request->input('operator_id', 0);
            if ($selectedOperatorId > 0) {
                $currentOperator = Operator::find($selectedOperatorId);
            }
        }
        
        // جلب وحدات التوليد للمشغل المحدد (للعرض في select)
        if ($currentOperator) {
            $generationUnitsQuery = GenerationUnit::where('operator_id', $currentOperator->id);
            
            // فلترة بوحدة التوليد إذا كانت محددة
            $generationUnitId = (int) $request->input('generation_unit_id', 0);
            if ($generationUnitId > 0) {
                $generationUnitsQuery->where('id', $generationUnitId);
            }
            
            $generationUnitsList = $generationUnitsQuery
                ->select('id', 'name', 'unit_code', 'status_id')
                ->with('statusDetail')
                ->orderBy('name')
                ->get();
        }

        return view('admin.generation-units.index', compact('generationUnits', 'operators', 'currentOperator', 'generationUnitsList'));
    }

    /**
     * Show the form for creating a new generation unit.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', GenerationUnit::class);

        $user = auth()->user();
        $operator = null;
        
        // تحديد المشغل حسب نوع المستخدم
        if ($user->isCompanyOwner()) {
            // صاحب المشغل
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        } elseif ($user->hasPermission('generation_units.create')) {
            // يوزر تابع للمشغل لديه صلاحية إضافة وحدات التوليد
            // جلب أول مشغل مرتبط به (إذا كان مرتبط بأكثر من مشغل، نأخذ الأول)
            $operator = $user->operators()->first();
            if (!$operator) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
        } else {
            abort(403, 'غير مصرح لك بإضافة وحدات التوليد. فقط صاحب المشغل أو المستخدمين التابعين للمشغل مع صلاحية إضافة وحدات التوليد يمكنهم الإضافة.');
        }
        
        // التحقق من اكتمال البيانات المطلوبة قبل إضافة وحدة التوليد
        $missingFields = [];
        if (empty($operator->name)) {
            $missingFields[] = 'اسم المشغل';
        }
        if (empty($operator->owner_name)) {
            $missingFields[] = 'اسم المالك';
        }
        if (empty($operator->owner_id_number)) {
            $missingFields[] = 'رقم هوية المالك';
        }
        if (empty($operator->operator_id_number)) {
            $missingFields[] = 'رقم هوية المشغل';
        }
        
        if (!empty($missingFields)) {
            return redirect()->route('admin.operators.profile')
                ->with('warning', 'يرجى إكمال البيانات التالية أولاً قبل إضافة وحدة التوليد: ' . implode('، ', $missingFields));
        }

        // جلب المحافظات والمدن
        $governorates = ConstantsHelper::get(1);
        $cities = collect();
        $selectedGovernorateId = null;

        if ($operator && $operator->governorate) {
            $selectedGovernorateCode = $operator->governorate->code();
            $governorateDetail = ConstantsHelper::findByCode(1, $selectedGovernorateCode);
            if ($governorateDetail) {
                $selectedGovernorateId = $governorateDetail->id;
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(15), // حالة الوحدة
            'operation_entity' => ConstantsHelper::get(2), // جهة التشغيل
            'synchronization_available' => ConstantsHelper::get(16), // إمكانية المزامنة
            'environmental_compliance_status' => ConstantsHelper::get(14), // حالة الامتثال البيئي
            'location' => ConstantsHelper::get(21), // موقع الخزان (تم تحديثه من 18 إلى 21)
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
            'tank_condition' => ConstantsHelper::get(22), // حالة الخزان
        ];

        // لا حاجة لـ allOperators لأن فقط CompanyOwner يمكنه إضافة وحدات التوليد
        return view('admin.generation-units.create', compact('operator', 'governorates', 'cities', 'selectedGovernorateId', 'constants'));
    }

    /**
     * Store a newly created generation unit.
     */
    public function store(StoreGenerationUnitRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', GenerationUnit::class);

        $user = auth()->user();
        $data = $request->validated();

        // تحديد المشغل حسب نوع المستخدم
        $operator = null;
        if ($user->isCompanyOwner()) {
            // صاحب المشغل
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
            $data['operator_id'] = $operator->id;
        } elseif ($user->hasPermission('generation_units.create')) {
            // يوزر تابع للمشغل لديه صلاحية إضافة وحدات التوليد
            $operator = $user->operators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك.');
            }
            $data['operator_id'] = $operator->id;
        } else {
            abort(403, 'غير مصرح لك بإضافة وحدات التوليد. فقط صاحب المشغل أو المستخدمين التابعين للمشغل مع صلاحية إضافة وحدات التوليد يمكنهم الإضافة.');
        }

        // إذا كان "نفس المالك"، جلب البيانات من المشغل
        if (isset($data['operation_entity_id']) && $operator) {
            // الحصول على ID ثابت "نفس المالك" من constant_master رقم 2
            $sameOwnerConstant = ConstantsHelper::findByCode(2, 'SAME_OWNER');
            if ($sameOwnerConstant && (int)$data['operation_entity_id'] === $sameOwnerConstant->id) {
                $data['owner_name'] = $operator->owner_name;
                $data['owner_id_number'] = $operator->owner_id_number;
                $data['operator_id_number'] = $operator->operator_id_number;
            }
        }

        // Get governorate code and city code to generate unit_code
        $governorateCode = null;
        $cityCode = null;
        
        if (isset($data['governorate_id']) && !empty($data['governorate_id'])) {
            $governorateDetail = \App\Models\ConstantDetail::find($data['governorate_id']);
            if ($governorateDetail && $governorateDetail->value) {
                try {
                    $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                    $governorateCode = $governorateEnum->code();
                } catch (\Exception $e) {
                    \Log::error('Failed to get governorate code', [
                        'governorate_id' => $data['governorate_id'],
                        'value' => $governorateDetail->value,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        // If governorate code not found and operator exists, try to get from operator
        if (!$governorateCode && $operator && $operator->governorate) {
            try {
                $governorateCode = $operator->governorate->code();
            } catch (\Exception $e) {
                \Log::error('Failed to get governorate code from operator', [
                    'operator_id' => $operator->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Get city code
        if (isset($data['city_id']) && !empty($data['city_id'])) {
            $cityDetail = \App\Models\ConstantDetail::find($data['city_id']);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }
        
        // If city code not found and operator exists, try to get from operator
        if (!$cityCode && $operator && $operator->city_id) {
            $cityDetail = $operator->cityDetail;
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }

        // تعيين قيم افتراضية للحقول الاختيارية
        if (!isset($data['generators_count']) || empty($data['generators_count'])) {
            $data['generators_count'] = 1; // افتراضي: مولد واحد
        }
        
        if (!isset($data['status_id']) || empty($data['status_id'])) {
            // جلب حالة "فعال" من الثوابت
            $activeStatus = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 15);
            })->where('code', 'ACTIVE')->first();
            
            if ($activeStatus) {
                $data['status_id'] = $activeStatus->id;
            }
        }

        // توليد رقم الوحدة وكود الوحدة تلقائياً
        if (!isset($data['unit_number']) || empty($data['unit_number'])) {
            // إذا كان governorate و city_id موجودان في الـ form، استخدمهما لتوليد unit_number
            if ($governorateCode && $cityCode) {
                $data['unit_number'] = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
            } else {
                // إذا لم يكن هناك operator_id، استخدم رقم افتراضي
                $data['unit_number'] = GenerationUnit::getNextUnitNumber($data['operator_id'] ?? null);
            }
        }

        if (!isset($data['unit_code']) || empty($data['unit_code'])) {
            // Use governorateCode and cityCode from form if available
            if ($governorateCode && $cityCode) {
                try {
                    $data['unit_code'] = GenerationUnit::generateUnitCodeByLocation($governorateCode, $cityCode, $data['unit_number'] ?? null);
                } catch (\Exception $e) {
                    \Log::error('Failed to generate unit code', [
                        'governorate_code' => $governorateCode,
                        'city_code' => $cityCode,
                        'unit_number' => $data['unit_number'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال المحافظة والمدينة بشكل صحيح.');
                }
            } else {
                // Cannot generate unit code without governorate and city
                $missingFields = [];
                if (!$governorateCode) {
                    $missingFields[] = 'المحافظة';
                }
                if (!$cityCode) {
                    $missingFields[] = 'المدينة';
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال: ' . implode(' و ', $missingFields) . '.');
            }
        }

        // التأكد من أن unit_code ليس null
        if (empty($data['unit_code'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل في توليد كود الوحدة. يرجى التأكد من إدخال المحافظة والمدينة بشكل صحيح.');
        }

        // Ensure operator_id is set
        if (!isset($data['operator_id']) || empty($data['operator_id'])) {
            if ($operator) {
                $data['operator_id'] = $operator->id;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'يجب تحديد المشغل.');
            }
        }

        // التحقق من المناطق الجغرافية ووحدات التوليد
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $latitude = (float) $data['latitude'];
            $longitude = (float) $data['longitude'];
            $operatorId = (int) $data['operator_id'];

            // التحقق من وجود وحدة توليد أخرى في نفس الإحداثيات أو قريبة جداً (في نطاق 100 متر)
            $minDistanceKm = 0.1; // 100 متر = 0.1 كم
            $existingUnit = GenerationUnit::where('id', '!=', 0) // Exclude current unit (doesn't exist yet in create)
                ->where('operator_id', '!=', $operatorId) // Different operator
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->first(function ($unit) use ($latitude, $longitude, $minDistanceKm) {
                    $distance = OperatorTerritory::calculateDistance(
                        (float) $unit->latitude,
                        (float) $unit->longitude,
                        $latitude,
                        $longitude
                    );
                    return $distance < $minDistanceKm;
                });

            if ($existingUnit) {
                $existingOperator = $existingUnit->operator;
                $distance = OperatorTerritory::calculateDistance(
                    (float) $existingUnit->latitude,
                    (float) $existingUnit->longitude,
                    $latitude,
                    $longitude
                );

                $errorMessage = sprintf(
                    'لا يمكن إضافة وحدة التوليد في هذا الموقع. يوجد وحدة توليد أخرى للمشغل "%s" (اسم الوحدة: "%s") في نفس الموقع أو قريبة جداً (المسافة: %.2f كم). الحد الأدنى للمسافة بين وحدات التوليد: %s كم.',
                    $existingOperator->name ?? 'مشغل آخر',
                    $existingUnit->name ?? 'غير محدد',
                    $distance,
                    $minDistanceKm
                );

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }

            // البحث عن منطقة لمشغل آخر تحتوي على هذه النقطة
            $existingTerritory = OperatorTerritory::findTerritoryContainingPoint(
                $latitude,
                $longitude,
                $operatorId
            );

            if ($existingTerritory) {
                $existingOperator = $existingTerritory->operator;
                $distance = OperatorTerritory::calculateDistance(
                    $existingTerritory->center_latitude,
                    $existingTerritory->center_longitude,
                    $latitude,
                    $longitude
                );

                $errorMessage = sprintf(
                    'لا يمكن إضافة وحدة التوليد في هذا الموقع. الموقع يقع ضمن منطقة جغرافية مملوكة للمشغل "%s" (نطاق %s كم). المسافة من مركز المنطقة: %s كم.',
                    $existingOperator->name ?? 'مشغل آخر',
                    $existingTerritory->radius_km,
                    $distance
                );

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }

            // إذا لم تكن هناك منطقة موجودة، إنشاء منطقة جديدة للمشغل الحالي
            // البحث عن منطقة قريبة للمشغل الحالي (في نطاق 100 كم)
            $operatorTerritories = OperatorTerritory::where('operator_id', $operatorId)->get();
            $nearbyTerritory = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($operatorTerritories as $territory) {
                $distance = OperatorTerritory::calculateDistance(
                    $territory->center_latitude,
                    $territory->center_longitude,
                    $latitude,
                    $longitude
                );

                // إذا كانت النقطة ضمن نطاق منطقة موجودة، لا حاجة لإنشاء منطقة جديدة
                if ($territory->containsPoint($latitude, $longitude)) {
                    $nearbyTerritory = $territory;
                    break;
                }

                // تتبع أقرب منطقة (في حالة الرغبة في توسيع المنطقة)
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearbyTerritory = $territory;
                }
            }

            // إذا لم تكن هناك منطقة قريبة، إنشاء منطقة جديدة
            if (!$nearbyTerritory || !$nearbyTerritory->containsPoint($latitude, $longitude)) {
                // حساب عدد المناطق الموجودة للمشغل لتحديد رقم المنطقة
                $territoriesCount = OperatorTerritory::where('operator_id', $operatorId)->count();
                $territoryNumber = $territoriesCount + 1;
                
                // حساب نصف القطر من المساحة إذا كانت المساحة محددة
                if (isset($data['territory_area_km2']) && $data['territory_area_km2'] > 0) {
                    // r = √(المساحة / π)
                    $areaKm2 = (float) $data['territory_area_km2'];
                    $radiusKm = sqrt($areaKm2 / M_PI);
                } elseif (isset($data['territory_radius_km']) && $data['territory_radius_km'] > 0) {
                    // إذا كان نصف القطر محدد مباشرة (للتوافق مع الكود القديم)
                    $radiusKm = (float) $data['territory_radius_km'];
                } else {
                    // سيستخدم القيمة الافتراضية من المشغل
                    $radiusKm = null;
                }
                
                // Get territory name from request, or use default
                $territoryName = $request->input('territory_name');
                if (empty($territoryName)) {
                    $territoryName = "منطقة #{$territoryNumber} - " . ($operator->name ?? "المشغل #{$operatorId}");
                }
                
                OperatorTerritory::createForOperator(
                    $operatorId,
                    $latitude,
                    $longitude,
                    $radiusKm,
                    $territoryName
                );
            }
        }
        
        // Track users
        $data['created_by'] = $user->id;
        $data['last_updated_by'] = $user->id;

        // معالجة خزانات الوقود
        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);
        unset($data['external_fuel_tank']);
        unset($data['fuel_tanks_count']);

        $generationUnit = GenerationUnit::create($data);

        // إضافة خزانات الوقود
        if (!empty($fuelTanksData)) {
            foreach ($fuelTanksData as $index => $tankData) {
                $tankCode = FuelTank::getNextTankCode($generationUnit->id);
                
                FuelTank::create([
                    'generation_unit_id' => $generationUnit->id,
                    'tank_code' => $tankCode,
                    'capacity' => $tankData['capacity'] ?? null,
                    'location_id' => $tankData['location_id'] ?? null,
                    'filtration_system_available' => $tankData['filtration_system_available'] ?? false,
                    'condition_id' => $tankData['condition_id'] ?? null,
                    'material_id' => $tankData['material_id'] ?? null,
                    'usage_id' => $tankData['usage_id'] ?? null,
                    'measurement_method_id' => $tankData['measurement_method_id'] ?? null,
                    'order' => $index + 1,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء وحدة التوليد بنجاح.',
                'generation_unit' => $generationUnit,
            ]);
        }

        return redirect()->route('admin.generation-units.index')
            ->with('success', 'تم إنشاء وحدة التوليد بنجاح.');
    }

    /**
     * Display the specified generation unit.
     */
    public function show(GenerationUnit $generationUnit): View
    {
        $this->authorize('view', $generationUnit);

        $generationUnit->load([
            'operator',
            'generators',
            'fuelTanks',
            'statusDetail',
            'operationEntityDetail',
            'synchronizationAvailableDetail',
            'environmentalComplianceStatusDetail',
            'city',
            'governorateDetail',
            'creator',
            'updater'
        ]);

        return view('admin.generation-units.show', compact('generationUnit'));
    }

    /**
     * Display QR Code for generation unit.
     * Only the operator owner (CompanyOwner) can generate QR codes.
     */
    public function qrCode(GenerationUnit $generationUnit): View
    {
        // التحقق من الصلاحية - فقط صاحب المشغل يمكنه إنشاء QR Code
        $this->authorize('generateQrCode', $generationUnit);

        $generationUnit->load(['operator']);

        // إنشاء بيانات QR Code - استخدام URL يفتح معلومات الوحدة
        $qrData = route('qr.generation-unit', ['code' => $generationUnit->unit_code ?? 'GU-' . $generationUnit->id]);
        
        // مسار حفظ QR Code
        $qrCodePath = 'qr-codes/generation-units/' . $generationUnit->id . '.svg';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // التحقق من وجود QR Code محفوظ
        if (!file_exists($fullPath) || !$generationUnit->qr_code_generated_at) {
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
            $generationUnit->update(['qr_code_generated_at' => now()]);
        } else {
            // قراءة QR Code المحفوظ
            $qrCodeSvg = file_get_contents($fullPath);
        }

        // بيانات إضافية للعرض في الصفحة
        $qrInfo = [
            'type' => 'generation_unit',
            'id' => $generationUnit->id,
            'unit_code' => $generationUnit->unit_code,
            'name' => $generationUnit->name,
            'operator_id' => $generationUnit->operator_id,
            'operator_name' => $generationUnit->operator?->name,
        ];

        return view('admin.generation-units.qr-code', compact('generationUnit', 'qrCodeSvg', 'qrInfo'));
    }

    /**
     * Show the form for editing the specified generation unit.
     */
    public function edit(GenerationUnit $generationUnit): View
    {
        $this->authorize('update', $generationUnit);

        $operator = $generationUnit->operator;

        // جلب المحافظات والمدن
        $governorates = ConstantsHelper::get(1);
        $cities = collect();
        $selectedGovernorateId = null;

        if ($generationUnit->governorate_id) {
            $selectedGovernorateId = $generationUnit->governorate_id;
            $governorateDetail = \App\Models\ConstantDetail::find($generationUnit->governorate_id);
            if ($governorateDetail) {
                $cities = ConstantsHelper::getCitiesByGovernorate($governorateDetail->id);
            }
        }

        $constants = [
            'status' => ConstantsHelper::get(15), // حالة الوحدة
            'operation_entity' => ConstantsHelper::get(2), // جهة التشغيل
            'synchronization_available' => ConstantsHelper::get(16), // إمكانية المزامنة
            'environmental_compliance_status' => ConstantsHelper::get(14), // حالة الامتثال البيئي
            'location' => ConstantsHelper::get(21), // موقع الخزان (تم تحديثه من 18 إلى 21)
            'material' => ConstantsHelper::get(10), // مادة التصنيع
            'usage' => ConstantsHelper::get(11), // الاستخدام
            'measurement_method' => ConstantsHelper::get(19), // طريقة القياس
            'tank_condition' => ConstantsHelper::get(22), // حالة الخزان
        ];

        $generationUnit->load(['fuelTanks', 'statusDetail', 'operationEntityDetail', 'synchronizationAvailableDetail', 'environmentalComplianceStatusDetail', 'governorateDetail', 'city']);

        return view('admin.generation-units.edit', compact('generationUnit', 'operator', 'governorates', 'cities', 'selectedGovernorateId', 'constants'));
    }

    /**
     * Update the specified generation unit.
     */
    public function update(UpdateGenerationUnitRequest $request, GenerationUnit $generationUnit): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $generationUnit);

        $user = auth()->user();
        $data = $request->validated();

        // إذا كان "نفس المالك"، جلب البيانات من المشغل
        $operator = $generationUnit->operator;
        if (isset($data['operation_entity_id']) && $operator) {
            // الحصول على ID ثابت "نفس المالك" من constant_master رقم 2
            $sameOwnerConstant = ConstantsHelper::findByCode(2, 'SAME_OWNER');
            if ($sameOwnerConstant && (int)$data['operation_entity_id'] === $sameOwnerConstant->id) {
                $data['owner_name'] = $operator->owner_name;
                $data['owner_id_number'] = $operator->owner_id_number;
                $data['operator_id_number'] = $operator->operator_id_number;
            }
        }

        // الحصول على governorate code و city code لتوليد unit_code (إذا تم تغييرهما)
        $governorateCode = null;
        $cityCode = null;
        if (isset($data['governorate_id'])) {
            $governorateDetail = \App\Models\ConstantDetail::find($data['governorate_id']);
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $governorateCode = $governorateEnum->code();
            }
        } elseif ($generationUnit->governorate_id) {
            // إذا لم يتم تغيير المحافظة، استخدم القيمة الحالية
            $governorateDetail = \App\Models\ConstantDetail::find($generationUnit->governorate_id);
            if ($governorateDetail && $governorateDetail->value) {
                $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
                $governorateCode = $governorateEnum->code();
            }
        }

        if (isset($data['city_id'])) {
            $cityDetail = \App\Models\ConstantDetail::find($data['city_id']);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        } elseif ($generationUnit->city_id) {
            $cityDetail = \App\Models\ConstantDetail::find($generationUnit->city_id);
            if ($cityDetail && $cityDetail->code) {
                $cityCode = $cityDetail->code;
            }
        }

        // إذا تم تغيير المحافظة أو المدينة، إعادة توليد unit_code
        if ($governorateCode && $cityCode && 
            ($data['governorate_id'] != $generationUnit->governorate_id || 
             (isset($data['city_id']) && $data['city_id'] != $generationUnit->city_id))) {
            $data['unit_number'] = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
            $data['unit_code'] = GenerationUnit::generateUnitCodeByLocation($governorateCode, $cityCode, $data['unit_number']);
        }

        // لا حاجة لتحويل synchronization_available لأنه أصبح ID الآن

        // التحقق من المناطق الجغرافية ووحدات التوليد (فقط إذا تم تغيير الإحداثيات)
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $latitude = (float) $data['latitude'];
            $longitude = (float) $data['longitude'];
            $operatorId = (int) $generationUnit->operator_id;

            // التحقق فقط إذا تغيرت الإحداثيات
            $coordinatesChanged = 
                abs($latitude - (float) $generationUnit->latitude) > 0.0001 ||
                abs($longitude - (float) $generationUnit->longitude) > 0.0001;

            if ($coordinatesChanged) {
                // التحقق من وجود وحدة توليد أخرى في نفس الإحداثيات أو قريبة جداً (في نطاق 100 متر)
                $minDistanceKm = 0.1; // 100 متر = 0.1 كم
                $existingUnit = GenerationUnit::where('id', '!=', $generationUnit->id) // Exclude current unit
                    ->where('operator_id', '!=', $operatorId) // Different operator
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get()
                    ->first(function ($unit) use ($latitude, $longitude, $minDistanceKm) {
                        $distance = OperatorTerritory::calculateDistance(
                            (float) $unit->latitude,
                            (float) $unit->longitude,
                            $latitude,
                            $longitude
                        );
                        return $distance < $minDistanceKm;
                    });

                if ($existingUnit) {
                    $existingOperator = $existingUnit->operator;
                    $distance = OperatorTerritory::calculateDistance(
                        (float) $existingUnit->latitude,
                        (float) $existingUnit->longitude,
                        $latitude,
                        $longitude
                    );

                    $errorMessage = sprintf(
                        'لا يمكن نقل وحدة التوليد إلى هذا الموقع. يوجد وحدة توليد أخرى للمشغل "%s" (اسم الوحدة: "%s") في نفس الموقع أو قريبة جداً (المسافة: %.2f كم). الحد الأدنى للمسافة بين وحدات التوليد: %s كم.',
                        $existingOperator->name ?? 'مشغل آخر',
                        $existingUnit->name ?? 'غير محدد',
                        $distance,
                        $minDistanceKm
                    );

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                        ], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMessage);
                }

                // البحث عن منطقة لمشغل آخر تحتوي على هذه النقطة
                $existingTerritory = OperatorTerritory::findTerritoryContainingPoint(
                    $latitude,
                    $longitude,
                    $operatorId
                );

                if ($existingTerritory) {
                    $existingOperator = $existingTerritory->operator;
                    $distance = OperatorTerritory::calculateDistance(
                        $existingTerritory->center_latitude,
                        $existingTerritory->center_longitude,
                        $latitude,
                        $longitude
                    );

                    $errorMessage = sprintf(
                        'لا يمكن تحديث موقع وحدة التوليد. الموقع الجديد يقع ضمن منطقة جغرافية مملوكة للمشغل "%s" (نطاق %s كم). المسافة من مركز المنطقة: %s كم.',
                        $existingOperator->name ?? 'مشغل آخر',
                        $existingTerritory->radius_km,
                        $distance
                    );

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                        ], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMessage);
                }

                // إذا لم تكن هناك منطقة موجودة، إنشاء منطقة جديدة للمشغل الحالي
                $operatorTerritories = OperatorTerritory::where('operator_id', $operatorId)->get();
                $nearbyTerritory = null;

                foreach ($operatorTerritories as $territory) {
                    if ($territory->containsPoint($latitude, $longitude)) {
                        $nearbyTerritory = $territory;
                        break;
                    }
                }

                // إذا لم تكن هناك منطقة قريبة، إنشاء منطقة جديدة
                if (!$nearbyTerritory) {
                    // حساب عدد المناطق الموجودة للمشغل لتحديد رقم المنطقة
                    $territoriesCount = OperatorTerritory::where('operator_id', $operatorId)->count();
                    $territoryNumber = $territoriesCount + 1;
                    
                    // حساب نصف القطر من المساحة إذا كانت المساحة محددة
                    if (isset($data['territory_area_km2']) && $data['territory_area_km2'] > 0) {
                        // r = √(المساحة / π)
                        $areaKm2 = (float) $data['territory_area_km2'];
                        $radiusKm = sqrt($areaKm2 / M_PI);
                    } elseif (isset($data['territory_radius_km']) && $data['territory_radius_km'] > 0) {
                        // إذا كان نصف القطر محدد مباشرة (للتوافق مع الكود القديم)
                        $radiusKm = (float) $data['territory_radius_km'];
                    } else {
                        // سيستخدم القيمة الافتراضية من المشغل
                        $radiusKm = null;
                    }
                    
                    // Get territory name from request, or use default
                    $territoryName = $request->input('territory_name');
                    if (empty($territoryName)) {
                        $territoryName = "منطقة #{$territoryNumber} - " . ($operator->name ?? "المشغل #{$operatorId}");
                    }
                    
                    OperatorTerritory::createForOperator(
                        $operatorId,
                        $latitude,
                        $longitude,
                        $radiusKm,
                        $territoryName
                    );
                }
            }
        }

        // تتبع المستخدمين
        $data['last_updated_by'] = $user->id;

        // معالجة خزانات الوقود
        $fuelTanksData = $data['fuel_tanks'] ?? [];
        unset($data['fuel_tanks']);
        unset($data['external_fuel_tank']);
        unset($data['fuel_tanks_count']);

        $generationUnit->update($data);

        // حذف خزانات الوقود القديمة
        $generationUnit->fuelTanks()->delete();

        // إضافة خزانات الوقود الجديدة
        if (!empty($fuelTanksData)) {
            foreach ($fuelTanksData as $index => $tankData) {
                $tankCode = FuelTank::getNextTankCode($generationUnit->id);
                
                FuelTank::create([
                    'generation_unit_id' => $generationUnit->id,
                    'tank_code' => $tankCode,
                    'capacity' => $tankData['capacity'] ?? null,
                    'location_id' => $tankData['location_id'] ?? null,
                    'filtration_system_available' => $tankData['filtration_system_available'] ?? false,
                    'condition_id' => $tankData['condition_id'] ?? null,
                    'material_id' => $tankData['material_id'] ?? null,
                    'usage_id' => $tankData['usage_id'] ?? null,
                    'measurement_method_id' => $tankData['measurement_method_id'] ?? null,
                    'order' => $index + 1,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث وحدة التوليد بنجاح.',
                'generation_unit' => $generationUnit->fresh(),
            ]);
        }

        return redirect()->route('admin.generation-units.index')
            ->with('success', 'تم تحديث وحدة التوليد بنجاح.');
    }

    /**
     * Remove the specified generation unit.
     * يحذف الوحدة مع جميع المولدات والسجلات المرتبطة بها.
     */
    /**
     * التحقق من إمكانية حذف وحدة التوليد (API)
     */
    public function canDelete(Request $request, GenerationUnit $generationUnit): JsonResponse
    {
        $this->authorize('delete', $generationUnit);
        
        $deletionCheck = $generationUnit->canBeDeleted();
        
        return response()->json([
            'success' => true,
            'data' => $deletionCheck,
        ]);
    }

    public function destroy(Request $request, GenerationUnit $generationUnit): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $generationUnit);

        // التحقق من وجود سجلات مرتبطة تمنع الحذف
        $deletionCheck = $generationUnit->canBeDeleted();

        if (!$deletionCheck['can_delete']) {
            $relatedRecordsLabels = array_map(function($record) {
                return $record['label'] . ' (' . $record['count'] . ')';
            }, $deletionCheck['related_records']);

            $message = 'لا يمكن حذف وحدة التوليد لأنها تحتوي على: ' . implode('، ', $relatedRecordsLabels) . '.';

            // إذا كان هناك مولدات تحتوي على سجلات، أضف تفاصيل إضافية
            if (!empty($deletionCheck['generators_with_records'])) {
                $generatorsDetails = [];
                foreach ($deletionCheck['generators_with_records'] as $gen) {
                    $genRecords = array_map(function($r) {
                        return $r['label'] . ' (' . $r['count'] . ')';
                    }, $gen['related_records']);
                    $generatorsDetails[] = $gen['name'] . ' (' . $gen['generator_number'] . ') - ' . implode('، ', $genRecords);
                }
                $message .= ' المولدات التالية تحتوي على سجلات: ' . implode(' | ', $generatorsDetails) . '. يرجى حذف أو نقل هذه السجلات أولاً.';
            } else {
                $message .= ' يرجى حذف أو نقل هذه السجلات أولاً.';
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()
                ->with('error', $message);
        }

        // حذف خزانات الوقود (فقط إذا لم يكن هناك مولدات)
        $generationUnit->fuelTanks()->delete();

        // حذف سجلات كفاءة الوقود المرتبطة مباشرة بالوحدة (فقط إذا لم يكن هناك مولدات)
        FuelEfficiency::where('generation_unit_id', $generationUnit->id)->delete();

        // حذف الوحدة نفسها
        $generationUnit->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف وحدة التوليد بنجاح.',
            ]);
        }

        return redirect()->route('admin.generation-units.index')
            ->with('success', 'تم حذف وحدة التوليد بنجاح.');
    }

    /**
     * Get operator data for auto-filling generation unit form.
     * This method allows access even if operator is not approved, as generation units
     * and generators should be accessible regardless of approval status.
     */
    /**
     * Get generation units by operator ID (for filters)
     */
    public function getGenerationUnitsByOperator(Request $request, int $operatorId): JsonResponse
    {
        $user = auth()->user();
        
        // التحقق من الصلاحيات
        $operator = Operator::find($operatorId);
        if (!$operator) {
            return response()->json([
                'success' => false,
                'message' => 'المشغل غير موجود',
                'generation_units' => []
            ], 404);
        }

        // للمشغل والموظفين: التحقق من أن المشغل مرتبط بهم
        if ($user->isCompanyOwner()) {
            $userOperator = $user->ownedOperators()->first();
            if (!$userOperator || $userOperator->id !== $operatorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول لهذا المشغل',
                    'generation_units' => []
                ], 403);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperatorIds = $user->operators()->pluck('operators.id')->toArray();
            if (!in_array($operatorId, $userOperatorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول لهذا المشغل',
                    'generation_units' => []
                ], 403);
            }
        }

        // فلترة حسب الحالة (إذا كانت محددة)
        $statusId = (int) $request->input('status_id', 0);
        $status = trim((string) $request->input('status', ''));
        
        $query = GenerationUnit::where('operator_id', $operatorId);
        
        if ($statusId > 0) {
            $query->where('status_id', $statusId);
        } elseif ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $statusConstant = \App\Models\ConstantDetail::whereHas('master', function($q) {
                $q->where('constant_number', 15);
            })->where('code', strtoupper($status) === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE')->first();
            
            if ($statusConstant) {
                $query->where('status_id', $statusConstant->id);
            }
        }

        $generationUnits = $query->select('id', 'name', 'unit_code', 'status_id')
            ->with('statusDetail')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'generation_units' => $generationUnits->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'unit_code' => $unit->unit_code,
                    'status_id' => $unit->status_id,
                    'status_label' => $unit->statusDetail?->label ?? 'غير محدد',
                    'status_code' => $unit->statusDetail?->code ?? null,
                ];
            })
        ]);
    }

    public function getOperatorData(Operator $operator): JsonResponse
    {
        $user = auth()->user();

        // Allow Super Admin and Admin to access any operator
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return response()->json([
                'success' => true,
                'operator' => [
                    'owner_name' => $operator->owner_name,
                    'owner_id_number' => $operator->owner_id_number,
                    'operator_id_number' => $operator->operator_id_number,
                    'phone' => $operator->phone,
                ],
            ]);
        }

        // Allow Company Owner to access their own operator (even if not approved)
        if ($user->isCompanyOwner() && $user->ownsOperator($operator)) {
            return response()->json([
                'success' => true,
                'operator' => [
                    'owner_name' => $operator->owner_name,
                    'owner_id_number' => $operator->owner_id_number,
                    'operator_id_number' => $operator->operator_id_number,
                    'phone' => $operator->phone,
                ],
            ]);
        }

        // Allow Employee and Technician to access operators they belong to
        if (($user->isEmployee() || $user->isTechnician()) && $user->belongsToOperator($operator)) {
            return response()->json([
                'success' => true,
                'operator' => [
                    'owner_name' => $operator->owner_name,
                    'owner_id_number' => $operator->owner_id_number,
                    'operator_id_number' => $operator->operator_id_number,
                    'phone' => $operator->phone,
                ],
            ]);
        }

        // Default: check permission but don't require approval
        if ($user->hasPermission('operators.view') && $user->belongsToOperator($operator)) {
            return response()->json([
                'success' => true,
                'operator' => [
                    'owner_name' => $operator->owner_name,
                    'owner_id_number' => $operator->owner_id_number,
                    'operator_id_number' => $operator->operator_id_number,
                    'phone' => $operator->phone,
                ],
            ]);
        }

        // If none of the above, deny access
        abort(403, 'غير مصرح لك بالوصول إلى بيانات هذا المشغل.');
    }

    /**
     * جلب جميع المناطق الجغرافية لعرضها على الخريطة
     */
    public function getAllTerritories(Request $request): JsonResponse
    {
        $user = auth()->user();
        $operatorId = null;

        // تحديد المشغل الحالي
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operatorId = $operator->id;
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operator = $user->operators()->first();
            if ($operator) {
                $operatorId = $operator->id;
            }
        } else {
            // للأدوار الأخرى: يمكن جلب operator_id من الطلب
            $operatorId = (int) $request->input('operator_id', 0);
        }

        $territories = OperatorTerritory::with('operator')
            ->get()
            ->map(function($territory) use ($operatorId) {
                $operator = $territory->operator;
                return [
                    'id' => $territory->id,
                    'operator_id' => $territory->operator_id,
                    'operator_name' => $operator->name ?? 'غير محدد',
                    'owner_name' => $operator->owner_name ?? 'غير محدد',
                    'center_latitude' => (float) $territory->center_latitude,
                    'center_longitude' => (float) $territory->center_longitude,
                    'radius_km' => (float) $territory->radius_km,
                    'name' => $territory->name,
                    'is_current_operator' => $territory->operator_id == $operatorId,
                ];
            });

        return response()->json([
            'success' => true,
            'territories' => $territories,
            'current_operator_id' => $operatorId,
        ]);
    }

    /**
     * التحقق من أن الموقع المحدد متاح (لا يقع ضمن منطقة لمشغل آخر ولا يوجد وحدة توليد قريبة)
     */
    public function checkTerritory(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'operator_id' => 'required|exists:operators,id',
            'generation_unit_id' => 'nullable|exists:generation_units,id', // For update operations
        ]);

        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');
        $operatorId = (int) $request->input('operator_id');
        $generationUnitId = $request->input('generation_unit_id');

        // التحقق من وجود وحدة توليد أخرى في نفس الإحداثيات أو قريبة جداً (في نطاق 100 متر)
        $minDistanceKm = 0.1; // 100 متر = 0.1 كم
        $query = GenerationUnit::where('operator_id', '!=', $operatorId) // Different operator
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Exclude current unit if updating
        if ($generationUnitId) {
            $query->where('id', '!=', $generationUnitId);
        }

        $existingUnit = $query->get()
            ->first(function ($unit) use ($latitude, $longitude, $minDistanceKm) {
                $distance = OperatorTerritory::calculateDistance(
                    (float) $unit->latitude,
                    (float) $unit->longitude,
                    $latitude,
                    $longitude
                );
                return $distance < $minDistanceKm;
            });

        if ($existingUnit) {
            $existingOperator = $existingUnit->operator;
            $distance = OperatorTerritory::calculateDistance(
                (float) $existingUnit->latitude,
                (float) $existingUnit->longitude,
                $latitude,
                $longitude
            );

            return response()->json([
                'success' => false,
                'available' => false,
                'message' => sprintf(
                    'يوجد وحدة توليد أخرى للمشغل "%s" (اسم الوحدة: "%s") في نفس الموقع أو قريبة جداً (المسافة: %.2f كم). الحد الأدنى للمسافة بين وحدات التوليد: %s كم.',
                    $existingOperator->name ?? 'مشغل آخر',
                    $existingUnit->name ?? 'غير محدد',
                    $distance,
                    $minDistanceKm
                ),
                'conflict_type' => 'generation_unit',
                'conflict_data' => [
                    'generation_unit_id' => $existingUnit->id,
                    'generation_unit_name' => $existingUnit->name ?? 'غير محدد',
                    'operator_id' => $existingOperator->id ?? null,
                    'operator_name' => $existingOperator->name ?? 'غير محدد',
                    'latitude' => (float) $existingUnit->latitude,
                    'longitude' => (float) $existingUnit->longitude,
                    'distance' => $distance,
                ],
            ]);
        }

        // البحث عن منطقة لمشغل آخر تحتوي على هذه النقطة
        $existingTerritory = OperatorTerritory::findTerritoryContainingPoint(
            $latitude,
            $longitude,
            $operatorId
        );

        if ($existingTerritory) {
            $existingOperator = $existingTerritory->operator;
            $distance = OperatorTerritory::calculateDistance(
                $existingTerritory->center_latitude,
                $existingTerritory->center_longitude,
                $latitude,
                $longitude
            );

            return response()->json([
                'success' => false,
                'available' => false,
                'message' => sprintf(
                    'الموقع يقع ضمن منطقة جغرافية مملوكة للمشغل "%s" (نطاق %s كم). المسافة من مركز المنطقة: %s كم.',
                    $existingOperator->name ?? 'مشغل آخر',
                    $existingTerritory->radius_km,
                    $distance
                ),
                'conflict_type' => 'territory',
                'territory' => [
                    'id' => $existingTerritory->id,
                    'operator_id' => $existingTerritory->operator_id,
                    'operator_name' => $existingOperator->name ?? 'غير محدد',
                    'center_latitude' => (float) $existingTerritory->center_latitude,
                    'center_longitude' => (float) $existingTerritory->center_longitude,
                    'radius_km' => (float) $existingTerritory->radius_km,
                    'distance' => $distance,
                ],
            ]);
        }

        // الحصول على نصف القطر من المشغل
        $operator = Operator::find($operatorId);
        $radiusKm = $operator && $operator->territory_radius_km 
            ? (float) $operator->territory_radius_km 
            : 5.0; // Default to 5km

        return response()->json([
            'success' => true,
            'available' => true,
            'message' => 'الموقع متاح لإضافة وحدة التوليد.',
            'operator_radius_km' => $radiusKm,
        ]);
    }

}

