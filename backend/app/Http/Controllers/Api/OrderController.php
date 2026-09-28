<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['items.book:id,slug,title,cover_path,author', 'shippingAddress', 'delivery'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        $order->load(['items.book:id,slug,title,cover_path,author', 'shippingAddress', 'delivery', 'mpesaTransactions']);
        return response()->json(['data' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.type' => 'required|in:digital,physical',
            'items.*.quantity' => 'required|integer|min:1|max:20',

            'customer_name' => 'required|string|max:120',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string|max:20',

            'shipping' => 'nullable|array',
            'shipping.recipient_name' => 'required_with:shipping|string|max:120',
            'shipping.phone' => 'required_with:shipping|string|max:20',
            'shipping.address' => 'required_with:shipping|string|max:200',
            'shipping.city' => 'required_with:shipping|string|max:80',
            'shipping.region' => 'nullable|string|max:80',
            'shipping.postal_code' => 'nullable|string|max:20',
            'shipping.country' => 'nullable|string|max:60',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($data, $user) {
            $subtotal = 0;
            $needsShipping = false;
            $bookIds = collect($data['items'])->pluck('book_id')->unique();
            $books = Book::whereIn('id', $bookIds)->get()->keyBy('id');

            $lines = [];
            foreach ($data['items'] as $line) {
                $book = $books[$line['book_id']];
                $isPhysical = $line['type'] === 'physical';

                if ($isPhysical && ! $book->has_physical) {
                    abort(422, "'{$book->title}' is not available in print.");
                }
                if (! $isPhysical && ! $book->has_digital) {
                    abort(422, "'{$book->title}' is not available as a digital download.");
                }

                $unit = $isPhysical ? (float) $book->physical_price : (float) $book->digital_price;
                $lineTotal = $unit * $line['quantity'];
                $subtotal += $lineTotal;
                if ($isPhysical) $needsShipping = true;

                $lines[] = [
                    'book_id' => $book->id,
                    'type' => $line['type'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                    'title_snapshot' => $book->title,
                ];
            }

            $shipping = $needsShipping ? 4.99 : 0.0;

            $order = Order::create([
                'reference' => 'BP-'.strtoupper(Str::random(6)),
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => 0,
                'total' => $subtotal + $shipping,
                'currency' => 'KES',
                'status' => 'pending',
                'payment_method' => 'mpesa',
                'payment_status' => 'pending',
                'needs_shipping' => $needsShipping,
            ]);

            $order->items()->createMany($lines);

            if ($needsShipping) {
                $ship = $data['shipping'] ?? null;
                abort_if(! $ship, 422, 'Shipping address is required for physical items.');
                $order->shippingAddress()->create([
                    'recipient_name' => $ship['recipient_name'],
                    'phone' => $ship['phone'],
                    'address' => $ship['address'],
                    'city' => $ship['city'],
                    'region' => $ship['region'] ?? null,
                    'postal_code' => $ship['postal_code'] ?? null,
                    'country' => $ship['country'] ?? 'Kenya',
                ]);
            }

            return response()->json([
                'data' => $order->load(['items.book:id,slug,title,cover_path,author', 'shippingAddress']),
            ], 201);
        });
    }
}
