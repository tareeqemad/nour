<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstantMasterRequest;
use App\Http\Requests\Admin\UpdateConstantMasterRequest;
use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConstantMasterController extends Controller
{
    /**
     * Display paginated list of constants with search and status filters.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', ConstantMaster::class);

        $query = ConstantMaster::withCount('allDetails');

        // Search filter (using 'search' or 'q' parameter)
        $search = trim((string) ($request->input('search') ?? $request->input('q', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('constant_number', 'like', "%{$search}%")
                    ->orWhere('constant_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $constants = $query->orderBy('order')
            ->orderBy('constant_number')
            ->paginate(100);

        if ($request->ajax() || $request->has('ajax')) {
            $totalActive = ConstantMaster::where('is_active', true)->count();
            $totalInactive = ConstantMaster::where('is_active', false)->count();
            $totalDetails = ConstantMaster::withCount('allDetails')->get()->sum('all_details_count');

            // Return HTML for tbody rows
            $html = view('admin.constants.partials.tbody-rows', compact('constants'))->render();
            $pagination = view('admin.constants.partials.pagination', compact('constants'))->render();
            $modals = view('admin.constants.partials.modals', compact('constants'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $constants->total(),
                'stats' => [
                    'total' => $constants->total(),
                    'active' => $totalActive,
                    'inactive' => $totalInactive,
                    'details' => $totalDetails,
                ],
                'modals' => $modals,
            ]);
        }

        return view('admin.constants.index', compact('constants'));
    }

    /**
     * Show form for creating a new constant master record entry.
     */
    public function create(): View
    {
        $this->authorize('create', ConstantMaster::class);

        $existingConstants = ConstantMaster::select('constant_number', 'constant_name', 'order')
            ->orderBy('order')
            ->orderBy('constant_number')
            ->get();

        return view('admin.constants.create', compact('existingConstants'));
    }

    /**
     * Get constant data by number (API endpoint).
     */
    public function getByNumber(Request $request, int $number): JsonResponse
    {
        $this->authorize('viewAny', ConstantMaster::class);

        $constant = ConstantMaster::where('constant_number', $number)
            ->with('allDetails')
            ->first();

        if (!$constant) {
            return response()->json([
                'success' => false,
                'message' => 'الثابت غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'constant_number' => $constant->constant_number,
                'constant_name' => $constant->constant_name,
                'description' => $constant->description,
                'is_active' => $constant->is_active,
                'order' => $constant->order,
                'details' => $constant->allDetails->map(function ($detail) {
                    return [
                        'label' => $detail->label,
                        'code' => $detail->code,
                        'value' => $detail->value,
                        'notes' => $detail->notes,
                        'is_active' => $detail->is_active,
                        'order' => $detail->order,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Store newly created constant master record in database.
     */
    public function store(StoreConstantMasterRequest $request): RedirectResponse|JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $details = $validated['details'] ?? [];

            // Create the constant master
            $constantMaster = ConstantMaster::create([
                'constant_number' => $validated['constant_number'],
                'constant_name' => $validated['constant_name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'order' => $validated['order'] ?? 0,
            ]);

            // Create details if provided
            if (!empty($details)) {
                foreach ($details as $detail) {
                    ConstantDetail::create([
                        'constant_master_id' => $constantMaster->id,
                        'label' => $detail['label'],
                        'code' => $detail['code'] ?? null,
                        'value' => $detail['value'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'is_active' => $detail['is_active'] ?? true,
                        'order' => $detail['order'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            $message = 'تم إنشاء الثابت بنجاح';
            if (!empty($details)) {
                $message .= ' مع ' . count($details) . ' تفصيل';
            }
            $message .= '.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'id' => $constantMaster->id,
                        'constant' => $constantMaster,
                    ],
                ]);
            }

            return redirect()->route('admin.constants.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display detailed information about the specified constant master record.
     */
    public function show(Request $request, ConstantMaster $constant): View|JsonResponse
    {
        $this->authorize('view', $constant);

        $query = $constant->allDetails();

        // Search filter
        $search = trim((string) ($request->input('search') ?? $request->input('q', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $details = $query->orderBy('order')->orderBy('id')->paginate(15);

        if ($request->ajax() || $request->has('ajax')) {
            $html = view('admin.constants.partials.details-tbody-rows', compact('details'))->render();
            $pagination = view('admin.constants.partials.details-pagination', compact('details'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $details->total(),
            ]);
        }

        return view('admin.constants.show', compact('constant', 'details'));
    }

    /**
     * Show form for editing the specified constant master record.
     */
    public function edit(ConstantMaster $constant): View
    {
        $this->authorize('update', $constant);

        $constant->load('allDetails');

        return view('admin.constants.edit', compact('constant'));
    }

    /**
     * Update the specified constant master record data in database.
     */
    public function update(UpdateConstantMasterRequest $request, ConstantMaster $constant): RedirectResponse|JsonResponse
    {
        $constant->update([
            'constant_number' => $request->validated('constant_number'),
            'constant_name' => $request->validated('constant_name'),
            'description' => $request->validated('description'),
            'is_active' => $request->validated('is_active', true),
            'order' => $request->validated('order', $constant->order),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الثابت بنجاح.',
            ]);
        }

        return redirect()->route('admin.constants.index')
            ->with('success', 'تم تحديث الثابت بنجاح.');
    }

    /**
     * Remove constant master record after checking for related details.
     */
    public function destroy(ConstantMaster $constant): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $constant);

        if ($constant->allDetails()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الثابت لأنه يحتوي على تفاصيل مرتبطة.',
                ], 403);
            }

            return redirect()->route('admin.constants.index')
                ->with('error', 'لا يمكن حذف الثابت لأنه يحتوي على تفاصيل مرتبطة.');
        }

        $constant->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الثابت بنجاح.',
            ]);
        }

        return redirect()->route('admin.constants.index')
            ->with('success', 'تم حذف الثابت بنجاح.');
    }
}
