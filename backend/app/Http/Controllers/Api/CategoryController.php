<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Category::query()
                ->withCount('books')
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'slug' => $c->slug,
                    'name' => $c->name,
                    'description' => $c->description,
                    'books_count' => $c->books_count,
                ]),
        ]);
    }
}
