<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\LibraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()->with('category')->where('is_published', true);

        if ($q = $request->string('q')->toString()) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('author', 'like', "%{$q}%");
            });
        }
        if ($cat = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $cat));
        }
        if ($request->string('format')->toString() === 'digital') $query->where('has_digital', true);
        if ($request->string('format')->toString() === 'physical') $query->where('has_physical', true);
        if ($rating = (float) $request->input('rating')) $query->where('rating', '>=', $rating);
        if ($tag = $request->string('filter')->toString()) $query->whereJsonContains('tags', $tag);
        if ($request->boolean('featured')) $query->where('is_featured', true);

        $sort = $request->string('sort', 'popular')->toString();
        match ($sort) {
            'newest' => $query->orderByDesc('published_at'),
            'rating' => $query->orderByDesc('rating'),
            'price-asc' => $query->orderBy('digital_price'),
            'price-desc' => $query->orderByDesc('digital_price'),
            default => $query->orderByDesc('reviews_count'),
        };

        $perPage = min((int) $request->input('per_page', 24), 60);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => BookResource::collection($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Book $book): JsonResponse
    {
        abort_unless($book->is_published, 404);
        $book->load('category');

        $related = Book::query()
            ->with('category')
            ->where('is_published', true)
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->limit(4)
            ->get();

        return response()->json([
            'data' => new BookResource($book),
            'related' => BookResource::collection($related),
        ]);
    }

    public function downloadPdf(Request $request, Book $book): StreamedResponse
    {
        abort_unless($book->pdf_path && Storage::disk('public')->exists($book->pdf_path), 404, 'PDF not available.');

        $user = $request->user();
        $owns = $user
            ? LibraryItem::where('user_id', $user->id)->where('book_id', $book->id)->exists()
            : false;

        abort_unless($owns || $user?->isAdmin(), 403, 'Purchase the digital edition to download this PDF.');

        return Storage::disk('public')->download($book->pdf_path, "{$book->slug}.pdf");
    }
}
