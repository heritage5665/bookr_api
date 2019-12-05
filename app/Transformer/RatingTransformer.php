<?php

namespace App\Transformer;

use App\Rating;
use League\Fractal\TransformerAbstract;

/**
 * Class Rating Transformer
 * @package App\Transformer
 */

class RatingTransformer extends TransformerAbstract
{
    /**
     * transform a Rating
     *
     * @param Rating $rating
     * @return array
     */

    public function transform(Rating $rating)
    {
        return [
            'id' => $rating->id,
            'value' => $rating->value,
            'type' => $rating->rateable_type,
            'links' => [
                [
                    'rel' => 'author',
                    'href' => route('authors.show', ['id' => $rating->rateable_id])
                ]
            ],
            'created' => $rating->created_at->toIso8601String(),
            'updated' => $rating->updated_at->toIso8601String()

        ];
    }
}
