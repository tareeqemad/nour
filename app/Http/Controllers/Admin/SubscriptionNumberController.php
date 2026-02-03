<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\GenerationUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionNumberController extends Controller
{
    /**
     * معاينة رقم الاشتراك المقترح
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'generation_unit_id' => 'required|exists:generation_units,id',
            'phase_type' => 'required|integer|in:1,2',
        ]);

        try {
            $subscriptionNumber = Subscriber::generateSubscriptionNumber(
                $request->generation_unit_id,
                $request->phase_type
            );

            return response()->json([
                'success' => true,
                'subscription_number' => $subscriptionNumber,
                'message' => 'رقم الاشتراك المقترح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * إحصائيات أرقام الاشتراك لتوليفة معينة
     */
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'generation_unit_id' => 'required|exists:generation_units,id',
            'phase_type' => 'required|integer|in:1,2',
        ]);

        try {
            $stats = Subscriber::getSubscriptionStats(
                $request->generation_unit_id,
                $request->phase_type
            );

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}