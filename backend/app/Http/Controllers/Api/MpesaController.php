<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\MpesaTransaction;
use App\Models\Order;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaController extends Controller
{
    public function __construct(protected MpesaService $mpesa) {}

    public function initiate(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_if($order->payment_status === 'paid', 409, 'Order already paid.');

        $data = $request->validate([
            'phone' => 'required|string|min:9|max:15',
        ]);

        try {
            $response = $this->mpesa->stkPush(
                phone: $data['phone'],
                amount: (float) $order->total,
                reference: $order->reference,
                description: "Order {$order->reference}",
            );
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        $tx = MpesaTransaction::create([
            'order_id' => $order->id,
            'phone' => $this->mpesa->normalizePhone($data['phone']),
            'amount' => $order->total,
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
            'status' => 'initiated',
            'raw_response' => $response,
        ]);

        $order->update(['payment_status' => 'processing']);

        return response()->json([
            'message' => 'STK Push sent. Complete the payment on your phone.',
            'transaction_id' => $tx->id,
            'checkout_request_id' => $tx->checkout_request_id,
        ]);
    }

    public function status(MpesaTransaction $transaction): JsonResponse
    {
        return response()->json([
            'status' => $transaction->status,
            'result_desc' => $transaction->result_desc,
            'mpesa_receipt' => $transaction->mpesa_receipt,
            'order_status' => $transaction->order?->payment_status,
        ]);
    }

    /**
     * Safaricom Daraja calls this endpoint after an STK Push completes or fails.
     * The route is public (no auth) but the payload is validated by
     * matching the CheckoutRequestID to a pending transaction we started.
     */
    public function callback(Request $request): JsonResponse
    {
        $body = $request->all();
        Log::info('M-Pesa callback', $body);

        $stk = $body['Body']['stkCallback'] ?? null;
        if (! $stk) return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Ignored']);

        $checkoutId = $stk['CheckoutRequestID'] ?? null;
        $tx = $checkoutId ? MpesaTransaction::where('checkout_request_id', $checkoutId)->first() : null;
        if (! $tx) return response()->json(['ResultCode' => 0, 'ResultDesc' => 'No matching transaction']);

        $resultCode = (int) ($stk['ResultCode'] ?? -1);
        $resultDesc = $stk['ResultDesc'] ?? null;

        $items = collect($stk['CallbackMetadata']['Item'] ?? [])
            ->pluck('Value', 'Name');

        DB::transaction(function () use ($tx, $stk, $resultCode, $resultDesc, $items) {
            $tx->update([
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'mpesa_receipt' => $items['MpesaReceiptNumber'] ?? null,
                'transaction_date' => isset($items['TransactionDate'])
                    ? \DateTimeImmutable::createFromFormat('YmdHis', (string) $items['TransactionDate'])
                    : null,
                'raw_callback' => $stk,
                'status' => match ($resultCode) {
                    0 => 'success',
                    1032 => 'cancelled',
                    1037 => 'timeout',
                    default => 'failed',
                },
            ]);

            if ($resultCode === 0 && $tx->order) {
                $order = $tx->order;
                $order->update([
                    'payment_status' => 'paid',
                    'status' => $order->needs_shipping ? 'paid' : 'paid',
                    'paid_at' => now(),
                ]);

                // Grant digital library entitlements
                foreach ($order->items()->where('type', 'digital')->get() as $line) {
                    LibraryItem::firstOrCreate(
                        ['user_id' => $order->user_id, 'book_id' => $line->book_id],
                        ['order_id' => $order->id, 'progress' => 0],
                    );
                }

                // Create a delivery record if any physical items
                if ($order->needs_shipping) {
                    $order->delivery()->firstOrCreate([], ['status' => 'ready']);
                }
            } elseif ($tx->order) {
                $tx->order->update(['payment_status' => 'failed']);
            }
        });

        // Daraja expects a 200 with this envelope acknowledgment
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback processed']);
    }
}
