<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()->with('category');

        if ($q = $request->string('q')->toString()) {
            $query->where(fn ($w) => $w
                ->where('title', 'like', "%{$q}%")
                ->orWhere('author', 'like', "%{$q}%"));
        }
        if ($cat = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $cat));
        }
        if ($request->string('format')->toString() === 'digital') $query->where('has_digital', true);
        if ($request->string('format')->toString() === 'physical') $query->where('has_physical', true);

        $paginator = $query->orderByDesc('created_at')->paginate(30);

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
        return response()->json(['data' => new BookResource($book->load('category'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']);
        $slug = $data['slug'];
        $i = 1;
        while (Book::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $slug.'-'.++$i;
        }
        $data = $this->handleUploads($request, $data);

        $book = Book::create($data);
        return response()->json(['data' => new BookResource($book->load('category'))], 201);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $this->validated($request, $book);
        $data = $this->handleUploads($request, $data, $book);
        $book->update($data);
        return response()->json(['data' => new BookResource($book->load('category'))]);
    }

    public function destroy(Book $book): JsonResponse
    {
        if ($book->cover_path) Storage::disk('public')->delete($book->cover_path);
        if ($book->pdf_path) Storage::disk('public')->delete($book->pdf_path);
        $book->delete();
        return response()->json(['message' => 'Book deleted.']);
    }

    protected function validated(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'author' => 'required|string|max:120',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'pages' => 'nullable|integer|min:0',
            'language' => 'nullable|string|max:40',
            'published_at' => 'nullable|date',
            'isbn' => 'nullable|string|max:32',
            'has_digital' => 'required|boolean',
            'has_physical' => 'required|boolean',
            'digital_price' => 'nullable|numeric|min:0',
            'physical_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:40',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'pdf' => 'nullable|file|mimes:pdf|max:40960',
        ]);
    }

    protected function handleUploads(Request $request, array $data, ?Book $book = null): array
    {
        if ($request->hasFile('cover')) {
            if ($book?->cover_path) Storage::disk('public')->delete($book->cover_path);
            $data['cover_path'] = $request->file('cover')->store('books/covers', 'public');
        }
        if ($request->hasFile('pdf')) {
            if ($book?->pdf_path) Storage::disk('public')->delete($book->pdf_path);
            $data['pdf_path'] = $request->file('pdf')->store('books/pdfs', 'public');
        }
        unset($data['cover'], $data['pdf']);
        return $data;
    }
}
