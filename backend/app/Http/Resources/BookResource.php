<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'pages' => $this->pages,
            'language' => $this->language,
            'published_at' => $this->published_at?->toDateString(),
            'isbn' => $this->isbn,
            'has_digital' => $this->has_digital,
            'has_physical' => $this->has_physical,
            'digital_price' => (float) $this->digital_price,
            'physical_price' => (float) $this->physical_price,
            'stock' => $this->stock,
            'weight_grams' => $this->weight_grams,
            'cover' => $this->cover_url,
            'has_pdf' => (bool) $this->pdf_path,
            'is_featured' => $this->is_featured,
            'is_published' => $this->is_published,
            'tags' => $this->tags ?? [],
            'rating' => (float) $this->rating,
            'reviews' => $this->reviews_count,
            'category' => $this->whenLoaded('category', fn () => [
                'slug' => $this->category->slug,
                'name' => $this->category->name,
            ]),
            'category_slug' => $this->whenLoaded('category', fn () => $this->category->slug),
        ];
    }
}
