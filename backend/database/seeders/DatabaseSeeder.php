<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Users ----
        User::updateOrCreate(
            ['email' => 'admin@bookplug.io'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+254700000000',
            ],
        );
        User::updateOrCreate(
            ['email' => 'reader@bookplug.io'],
            [
                'name' => 'Ava Reader',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '+254700111222',
            ],
        );

        // ---- Categories ----
        $categories = collect([
            ['name' => 'Fiction', 'slug' => 'fiction'],
            ['name' => 'Non-fiction', 'slug' => 'non-fiction'],
            ['name' => 'Business', 'slug' => 'business'],
            ['name' => 'Technology', 'slug' => 'tech'],
            ['name' => 'Self-help', 'slug' => 'self-help'],
            ['name' => "Children's", 'slug' => 'children'],
            ['name' => 'Romance', 'slug' => 'romance'],
            ['name' => 'History', 'slug' => 'history'],
        ])->mapWithKeys(function ($c) {
            $cat = Category::updateOrCreate(['slug' => $c['slug']], ['name' => $c['name']]);
            return [$c['slug'] => $cat->id];
        });

        // ---- Books (mirrors frontend/src/data/books.js) ----
        $books = [
            ['slug' => 'the-quiet-forest', 'title' => 'The Quiet Forest', 'author' => 'Amelia Hart', 'cat' => 'fiction', 'rating' => 4.7, 'reviews' => 328, 'pages' => 312, 'pub' => '2024-03-14', 'dp' => 6.99, 'pp' => 19.99, 'phys' => true, 'feat' => true, 'tags' => ['bestseller','literary'], 'desc' => 'A haunting, luminous story about family, memory, and the wild places that shape us. Ideal for readers who loved Where the Crawdads Sing.'],
            ['slug' => 'algorithms-in-motion', 'title' => 'Algorithms in Motion', 'author' => 'Kwame Otieno', 'cat' => 'tech', 'rating' => 4.9, 'reviews' => 512, 'pages' => 428, 'pub' => '2025-01-05', 'dp' => 12.5, 'pp' => 32.0, 'phys' => true, 'feat' => true, 'tags' => ['staff pick'], 'desc' => 'A visual, project-driven guide to the algorithms every working engineer should know — from graphs to dynamic programming.'],
            ['slug' => 'the-founder-playbook', 'title' => 'The Founder Playbook', 'author' => 'Priya Menon', 'cat' => 'business', 'rating' => 4.5, 'reviews' => 210, 'pages' => 264, 'pub' => '2023-11-22', 'dp' => 8.99, 'pp' => 24.0, 'phys' => true, 'feat' => true, 'tags' => ['new'], 'desc' => 'Battle-tested lessons from twelve early-stage founders on hiring, fundraising, and building product velocity.'],
            ['slug' => 'small-brave-things', 'title' => 'Small Brave Things', 'author' => 'Léa Dumont', 'cat' => 'self-help', 'rating' => 4.6, 'reviews' => 187, 'pages' => 198, 'pub' => '2024-08-01', 'dp' => 5.99, 'pp' => 16.0, 'phys' => true, 'feat' => false, 'tags' => [], 'desc' => 'A gentle collection of essays for anyone rebuilding after a hard year. Practical, warm, and quietly transformative.'],
            ['slug' => 'kingdoms-of-salt', 'title' => 'Kingdoms of Salt', 'author' => 'Nadia Rashid', 'cat' => 'history', 'rating' => 4.8, 'reviews' => 92, 'pages' => 502, 'pub' => '2022-06-10', 'dp' => 10.0, 'pp' => 28.5, 'phys' => true, 'feat' => true, 'tags' => ['award winner'], 'desc' => 'A sweeping history of the Indian Ocean trade routes, told through the lives of merchants, monarchs, and monsoons.'],
            ['slug' => 'the-cinnamon-cat', 'title' => 'The Cinnamon Cat', 'author' => 'Fatima Suleiman', 'cat' => 'children', 'rating' => 4.9, 'reviews' => 74, 'pages' => 42, 'pub' => '2025-02-12', 'dp' => 3.5, 'pp' => 12.99, 'phys' => true, 'feat' => false, 'tags' => ['illustrated'], 'desc' => 'An illustrated picture book about a curious cat who discovers the spice market — perfect for ages 4–8.'],
            ['slug' => 'signal-and-noise', 'title' => 'Signal and Noise', 'author' => 'Daniel Björk', 'cat' => 'non-fiction', 'rating' => 4.4, 'reviews' => 141, 'pages' => 288, 'pub' => '2023-04-19', 'dp' => 7.5, 'pp' => 21.0, 'phys' => false, 'feat' => false, 'tags' => ['digital only'], 'desc' => 'How to think clearly in a noisy world. A working journalist unpacks the small habits that build a reliable point of view.'],
            ['slug' => 'moonlight-in-lamu', 'title' => 'Moonlight in Lamu', 'author' => 'Zawadi Kimani', 'cat' => 'romance', 'rating' => 4.3, 'reviews' => 268, 'pages' => 342, 'pub' => '2024-10-30', 'dp' => 5.5, 'pp' => 17.99, 'phys' => true, 'feat' => true, 'tags' => ['bestseller'], 'desc' => 'Two rival hoteliers, one week on the Swahili coast, and a monsoon that refuses to stay outside. A slow-burn coastal romance.'],
            ['slug' => 'the-language-of-cities', 'title' => 'The Language of Cities', 'author' => 'Rohan Gupta', 'cat' => 'non-fiction', 'rating' => 4.6, 'reviews' => 88, 'pages' => 356, 'pub' => '2022-09-05', 'dp' => 9.0, 'pp' => 25.0, 'phys' => true, 'feat' => false, 'tags' => [], 'desc' => 'What our streets, signs, and skylines say about who we are. An urbanist reads twelve cities across three continents.'],
            ['slug' => 'grit-and-code', 'title' => 'Grit and Code', 'author' => 'Sofia Alvarez', 'cat' => 'tech', 'rating' => 4.7, 'reviews' => 315, 'pages' => 220, 'pub' => '2025-05-20', 'dp' => 8.99, 'pp' => 22.5, 'phys' => true, 'feat' => true, 'tags' => ['new'], 'desc' => 'Career advice for engineers who don\'t fit the mold — from switching stacks mid-career to negotiating your first staff offer.'],
            ['slug' => 'the-weight-of-water', 'title' => 'The Weight of Water', 'author' => 'Ines Costa', 'cat' => 'fiction', 'rating' => 4.5, 'reviews' => 157, 'pages' => 388, 'pub' => '2023-07-11', 'dp' => 6.5, 'pp' => 19.5, 'phys' => true, 'feat' => false, 'tags' => [], 'desc' => 'A quiet novel about three women, one island, and a summer that will change what they thought family could mean.'],
            ['slug' => 'compounding', 'title' => 'Compounding', 'author' => 'Marcus Chen', 'cat' => 'business', 'rating' => 4.8, 'reviews' => 402, 'pages' => 240, 'pub' => '2024-01-16', 'dp' => 9.5, 'pp' => 26.0, 'phys' => true, 'feat' => true, 'tags' => ['staff pick'], 'desc' => 'Small decisions, taken consistently, are the closest thing to a superpower. A short book on habit-scale thinking.'],
        ];

        foreach ($books as $b) {
            Book::updateOrCreate(
                ['slug' => $b['slug']],
                [
                    'category_id' => $categories[$b['cat']],
                    'title' => $b['title'],
                    'author' => $b['author'],
                    'description' => $b['desc'],
                    'pages' => $b['pages'],
                    'language' => 'English',
                    'published_at' => $b['pub'],
                    'has_digital' => true,
                    'has_physical' => $b['phys'],
                    'digital_price' => $b['dp'],
                    'physical_price' => $b['pp'],
                    'stock' => $b['phys'] ? 25 : 0,
                    'is_featured' => $b['feat'],
                    'is_published' => true,
                    'tags' => $b['tags'],
                    'rating' => $b['rating'],
                    'reviews_count' => $b['reviews'],
                ],
            );
        }
    }
}
