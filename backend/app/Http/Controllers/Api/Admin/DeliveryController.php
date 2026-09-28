<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = Delivery::query()
            ->with(['order:id,reference,customer_name', 'order.shippingAddress:id,order_id,city,address'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $rows,
            'counts' => [
                'ready' => Delivery::where('status', 'ready')->count(),
                'in_transit' => Delivery::where('status', 'in_transit')->count(),
                'delivered' => Delivery::where('status', 'delivered')->count(),
            ],
        ]);
    }

    public function upsert(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->needs_shipping, 422, 'This order does not need shipping.');

        $data = $request->validate([
            'courier' => 'nullable|string|max:80',
            'tracking_number' => 'nullable|string|max:80',
            'status' => 'required|in:ready,in_transit,delivered,failed,returned',
            'eta' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $delivery = $order->delivery()->updateOrCreate([], $data);

        if ($data['status'] === 'in_transit' && ! $delivery->shipped_at) {
            $delivery->update(['shipped_at' => now()]);
            $order->update(['status' => 'shipped']);
        }
        if ($data['status'] === 'delivered') {
            $delivery->update(['delivered_at' => now()]);
            $order->update(['status' => 'delivered']);
        }

        return response()->json(['data' => $delivery->fresh()]);
    }
}
