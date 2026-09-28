<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->withCount('orders')
            ->withSum(['orders as spent' => fn ($q) => $q->where('status', '!=', 'refunded')], 'total');

        if ($role = $request->string('role')->toString()) $query->where('role', $role);
        if ($q = $request->string('q')->toString()) {
            $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"));
        }

        return response()->json([
            'data' => $query->orderByDesc('created_at')->paginate(30),
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['role' => 'required|in:user,admin']);
        $user->update($data);
        return response()->json(['data' => $user]);
    }
}
