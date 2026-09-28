<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\LibraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->library()
            ->with('book.category')
            ->orderByDesc('last_read_at')
            ->get();

        return response()->json([
            'data' => $items->map(fn (LibraryItem $it) => [
                'progress' => $it->progress,
                'last_read_at' => $it->last_read_at?->toIso8601String(),
                'book' => new BookResource($it->book),
            ]),
        ]);
    }

    public function updateProgress(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $item = LibraryItem::where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->firstOrFail();

        $item->update([
            'progress' => $data['progress'],
            'last_read_at' => now(),
        ]);

        return response()->json(['data' => $item]);
    }
}
