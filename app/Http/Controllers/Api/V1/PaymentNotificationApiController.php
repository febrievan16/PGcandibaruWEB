<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentNotificationApiController extends Controller
{
    public function __construct(
        public MidtransService $midtransService,
    ) {}

    /**
     * POST /api/v1/payments/notification
     * Handle Midtrans webhook callback with SHA512 signature verification (API.md §3.3).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '');
        $signatureKey = (string) $request->input('signature_key', '');
        $transactionStatus = (string) $request->input('transaction_status', '');

        if ($orderId === '' || $signatureKey === '' || ! $this->midtransService->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Signature key Midtrans tidak valid.',
                    'details' => (object) [],
                ],
            ], 403);
        }

        $exists = Order::where('order_code', $orderId)
            ->orWhere('midtrans_order_id', $orderId)
            ->exists();

        if (! $exists) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Order tidak ditemukan.',
                    'details' => (object) [],
                ],
            ], 404);
        }

        $this->midtransService->handleNotification([
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'payment_type' => (string) $request->input('payment_type', 'bank_transfer'),
            'transaction_id' => (string) $request->input('transaction_id', ''),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}

