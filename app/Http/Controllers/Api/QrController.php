<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrController extends Controller
{
    /**
     * قراءة QR Code للمولد
     * 
     * QR Code يحتوي على generator_number أو يمكن أن يكون URL
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        $qrCode = trim($request->input('qr_code'));

        // استخراج generator_number من QR code
        // QR code قد يكون: generator_number مباشرة أو URL يحتوي على generator_number
        $generatorNumber = $this->extractGeneratorNumber($qrCode);

        if (!$generatorNumber) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code غير صحيح.',
            ], 400);
        }

        // البحث عن المولد
        $generator = Generator::where('generator_number', $generatorNumber)
            ->with(['operator', 'generationUnit', 'statusDetail'])
            ->first();

        if (!$generator) {
            return response()->json([
                'success' => false,
                'message' => 'المولد غير موجود.',
            ], 404);
        }

        $user = $request->user();

        // تحديد نوع النموذج حسب دور المستخدم
        $formType = null;
        if ($user->isTechnician()) {
            $formType = 'maintenance';
        } elseif ($this->isCivilDefense($user)) {
            $formType = 'compliance_safety';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'generator' => [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number,
                    'operator' => $generator->operator ? [
                        'id' => $generator->operator->id,
                        'name' => $generator->operator->name,
                    ] : null,
                    'status' => $generator->statusDetail?->label ?? 'غير محدد',
                ],
                'form_type' => $formType,
                'can_access' => $formType !== null,
            ],
        ]);
    }

    /**
     * استخراج generator_number من QR code
     */
    private function extractGeneratorNumber(string $qrCode): ?string
    {
        // إذا كان QR code هو generator_number مباشرة
        if (preg_match('/^[A-Z0-9\-]+$/', $qrCode)) {
            return $qrCode;
        }

        // إذا كان QR code هو URL، استخراج generator_number من المسار
        if (preg_match('/\/qr\/generator\/([A-Z0-9\-]+)/', $qrCode, $matches)) {
            return $matches[1];
        }

        // محاولة استخراج من أي URL
        if (preg_match('/generator[_-]?number[=:]?([A-Z0-9\-]+)/i', $qrCode, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * التحقق من أن المستخدم هو دفاع مدني
     */
    private function isCivilDefense($user): bool
    {
        $roleName = $user->roleModel?->name ?? $user->role?->value;
        
        $hasPermission = method_exists($user, 'hasPermission') 
            ? $user->hasPermission('compliance_safety.create') 
            : false;
        
        return $roleName === 'civil_defense' 
            || $user->isEnergyAuthority() 
            || $hasPermission;
    }
}
