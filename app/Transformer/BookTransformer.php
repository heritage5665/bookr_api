<?php

namespace App\Transformer;

use League\Fractal\TransformerAbstract;
use App\Book;

class BookTransformer extends TransformerAbstract
{
    /**
     * Transform a Book into an array
     *
     * @param Book $book
     * @return array
     */

    public function transform(Book $book)
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'description' => $book->description,
            'author' => $book->author->name,
            'created' => $book->created_at->toIso8601String(),
            'updated' => $book->updated_at->toIso8601String(),

        ];
    }
}
