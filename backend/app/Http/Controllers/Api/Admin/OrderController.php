<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user:id,name,email', 'items', 'delivery']);

        if ($status = $request->string('status')->toString()) $query->where('status', $status);
        if ($q = $request->string('q')->toString()) {
            $query->where(fn ($w) => $w
                ->where('reference', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->orWhere('customer_email', 'like', "%{$q}%"));
        }

        $orders = $query->orderByDesc('created_at')->paginate(30);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'counts' => [
                    'all' => Order::count(),
                    'pending' => Order::where('status', 'pending')->count(),
                    'paid' => Order::where('status', 'paid')->count(),
                    'shipped' => Order::where('status', 'shipped')->count(),
                    'delivered' => Order::where('status', 'delivered')->count(),
                    'refunded' => Order::where('status', 'refunded')->count(),
                ],
                'revenue_month' => (float) Order::whereMonth('created_at', now()->month)
                    ->where('status', '!=', 'refunded')
                    ->sum('total'),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['user:id,name,email', 'items.book:id,slug,title,cover_path,author', 'shippingAddress', 'delivery', 'mpesaTransactions']);
        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,paid,shipped,delivered,refunded,cancelled',
        ]);
        $order->update($data);
        return response()->json(['data' => $order]);
    }
}
