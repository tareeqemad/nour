<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInspectionViolationCaseRequest;
use App\Http\Requests\Admin\UpdateInspectionViolationCaseRequest;
use App\Models\InspectionViolationCase;
use App\Models\Operator;
use App\Models\GenerationUnit;
use App\Helpers\ConstantsHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspectionViolationCaseController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', InspectionViolationCase::class);

        $user = Auth::user();
        $query = InspectionViolationCase::with(['governorateDetail', 'operator:id,name', 'generationUnit:id,name,unit_code']);

        if ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id');
            $query->whereIn('operator_id', $operatorIds);
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operatorIds = $user->operators()->pluck('operators.id');
            $query->whereIn('operator_id', $operatorIds);
        }

        if (($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) && $request->filled('operator_id')) {
            $query->where('operator_id', (int) $request->operator_id);
        }
        if ($request->filled('generation_unit_id')) {
            $query->where('generation_unit_id', (int) $request->generation_unit_id);
        }
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', (int) $request->governorate_id);
        }
        if ($request->filled('case_date_from')) {
            $query->whereDate('case_date', '>=', $request->case_date_from);
        }
        if ($request->filled('case_date_to')) {
            $query->whereDate('case_date', '<=', $request->case_date_to);
        }
        if ($request->filled('status') && in_array($request->status, ['1', '2', '3'])) {
            $query->where('status', (int) $request->status);
        }

        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subscription_number', 'like', "%{$search}%")
                    ->orWhere('subscriber_name', 'like', "%{$search}%")
                    ->orWhere('beneficiary_name', 'like', "%{$search}%")
                    ->orWhereHas('operator', fn ($o) => $o->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('generationUnit', fn ($g) => $g->where('name', 'like', "%{$search}%")->orWhere('unit_code', 'like', "%{$search}%"));
            });
        }

        $cases = $query->latest('case_date')->paginate(15);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.inspection-violation-cases.partials.list', compact('cases'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $cases->total(),
            ]);
        }

        $operators = collect();
        $generationUnits = collect();
        $generationUnitsByOperator = [];
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name', 'unit_number')->orderBy('name')->get();
            $selectedOperatorId = (int) $request->input('operator_id', 0);
            if ($selectedOperatorId > 0) {
                $generationUnits = GenerationUnit::where('operator_id', $selectedOperatorId)
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
            }
            foreach (GenerationUnit::select('id', 'name', 'unit_code', 'operator_id')->orderBy('unit_code')->get() as $gu) {
                $generationUnitsByOperator[$gu->operator_id][] = ['id' => $gu->id, 'name' => $gu->name, 'unit_code' => $gu->unit_code];
            }
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
                $generationUnits = $operator->generationUnits()
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperators = $user->operators;
            $operators = $userOperators;
            $generationUnits = GenerationUnit::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
        }

        $governorates = ConstantsHelper::get(1);

        return view('admin.inspection-violation-cases.index', compact('cases', 'operators', 'generationUnits', 'governorates', 'generationUnitsByOperator'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', InspectionViolationCase::class);

        $user = Auth::user();
        $operators = collect();
        $generationUnits = collect();
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
            if ($request->filled('operator_id')) {
                $generationUnits = GenerationUnit::where('operator_id', (int) $request->operator_id)
                    ->select('id', 'name', 'unit_code')->orderBy('name')->get();
            }
        } elseif ($user->isCompanyOwner()) {
            $op = $user->ownedOperators()->first();
            if ($op) {
                $operators = collect([$op]);
                $generationUnits = $op->generationUnits()->select('id', 'name', 'unit_code')->orderBy('name')->get();
            }
        }

        $governorates = ConstantsHelper::get(1);

        return view('admin.inspection-violation-cases.create', compact('operators', 'generationUnits', 'governorates'));
    }

    public function store(StoreInspectionViolationCaseRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', InspectionViolationCase::class);

        $data = $request->validated();
        $data['created_by'] = Auth::id();
        InspectionViolationCase::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم إضافة القضية بنجاح.']);
        }
        return redirect()->route('admin.inspection-violation-cases.index')->with('success', 'تم إضافة القضية بنجاح.');
    }

    public function edit(InspectionViolationCase $inspection_violation_case): View|RedirectResponse
    {
        $this->authorize('update', $inspection_violation_case);

        $user = Auth::user();
        $operators = collect();
        $generationUnits = collect();
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
            if ($inspection_violation_case->operator_id) {
                $generationUnits = GenerationUnit::where('operator_id', $inspection_violation_case->operator_id)
                    ->select('id', 'name', 'unit_code')->orderBy('name')->get();
            }
        } elseif ($user->isCompanyOwner()) {
            $op = $user->ownedOperators()->first();
            if ($op) {
                $operators = collect([$op]);
                $generationUnits = $op->generationUnits()->select('id', 'name', 'unit_code')->orderBy('name')->get();
            }
        }

        $governorates = ConstantsHelper::get(1);

        return view('admin.inspection-violation-cases.edit', compact('inspection_violation_case', 'operators', 'generationUnits', 'governorates'));
    }

    public function update(UpdateInspectionViolationCaseRequest $request, InspectionViolationCase $inspection_violation_case): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $inspection_violation_case);

        $inspection_violation_case->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث القضية بنجاح.']);
        }
        return redirect()->route('admin.inspection-violation-cases.index')->with('success', 'تم تحديث القضية بنجاح.');
    }

    public function destroy(Request $request, InspectionViolationCase $inspection_violation_case): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $inspection_violation_case);
        $inspection_violation_case->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم حذف القضية.']);
        }
        return redirect()->route('admin.inspection-violation-cases.index')->with('success', 'تم حذف القضية.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', InspectionViolationCase::class);

        $user = Auth::user();
        $query = InspectionViolationCase::with(['governorateDetail', 'operator:id,name', 'generationUnit:id,name,unit_code']);

        if ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id');
            $query->whereIn('operator_id', $operatorIds);
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operatorIds = $user->operators()->pluck('operators.id');
            $query->whereIn('operator_id', $operatorIds);
        }
        if (($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) && $request->filled('operator_id')) {
            $query->where('operator_id', (int) $request->operator_id);
        }
        if ($request->filled('generation_unit_id')) {
            $query->where('generation_unit_id', (int) $request->generation_unit_id);
        }
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', (int) $request->governorate_id);
        }
        if ($request->filled('case_date_from')) {
            $query->whereDate('case_date', '>=', $request->case_date_from);
        }
        if ($request->filled('case_date_to')) {
            $query->whereDate('case_date', '<=', $request->case_date_to);
        }
        if ($request->filled('status') && in_array($request->status, ['1', '2', '3'])) {
            $query->where('status', (int) $request->status);
        }
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subscription_number', 'like', "%{$search}%")
                    ->orWhere('subscriber_name', 'like', "%{$search}%")
                    ->orWhere('beneficiary_name', 'like', "%{$search}%");
            });
        }

        $cases = $query->latest('case_date')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $headers = ['المحافظة', 'رقم الاشتراك', 'اسم المشترك', 'اسم المنتفع', 'تاريخ القضية', 'حالة القضية', 'بيان القضية/التعدي'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        $row = 2;
        $labels = InspectionViolationCase::statusLabels();
        foreach ($cases as $c) {
            $sheet->setCellValue('A' . $row, $c->governorateDetail?->label ?? '');
            $sheet->setCellValue('B' . $row, $c->subscription_number ?? '');
            $sheet->setCellValue('C' . $row, $c->subscriber_name);
            $sheet->setCellValue('D' . $row, $c->beneficiary_name ?? '');
            $sheet->setCellValue('E' . $row, $c->case_date?->format('Y-m-d') ?? '');
            $sheet->setCellValue('F' . $row, $labels[$c->status] ?? $c->status);
            $sheet->setCellValue('G' . $row, $c->statement ?? '');
            $row++;
        }
        foreach (range('A', 'G') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'قضايا_التفتيش_والتعدي_' . date('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        $writer->save($tempPath);

        return response()->streamDownload(function () use ($tempPath) {
            echo file_get_contents($tempPath);
            @unlink($tempPath);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
