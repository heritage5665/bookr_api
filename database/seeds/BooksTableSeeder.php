<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BooksTableSeeder extends Seeder
{

    /**
     * Run the database seeds
     *
     * @return void
     */

    public function run()
    {
        factory(App\Author::class, 10)->create()->each(function ($author) {
            $bookCount = rand(1, 5);
            $author->ratings()->saveMany(
                factory(App\Rating::class, rand(20, 50))->make()
            );

            while ($bookCount > 0) {
                $book = factory(App\Book::class)->make();
                $author->books()->save($book);
                $book->ratings()->saveMany(
                    factory(App\Rating::class, rand(20, 50))->make()
                );
                $bookCount--;
            }
        });
    }
}
