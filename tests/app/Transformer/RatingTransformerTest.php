<?php

namespace Tests\App\Transformer;

use TestCase;
use App\Transformer\RatingTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;

class RatingTransformerTest extends TestCase
{
    use DatabaseMigrations;


    public function test_it_can_be_initialized()
    {
        $this->assertInstanceOf(RatingTransformer::class, new RatingTransformer());
    }

    public function test_it_can_transform_a_rating_for_an_author()
    {
        $subject = new RatingTransformer();
        $author = \factory(\App\Author::class)->create();
        $rating = $author->ratings()->save(
            \factory(\App\Rating::class)->make()
        );


        $actual = $subject->transform($rating);
        //  dd($rating);
        $this->assertEquals($rating->id, $actual['id']);
        $this->assertEquals($rating->value, $actual['value']);
        // $this->assertEquals($rating->rateable_id, $actual['rateable_id']);
        $this->assertEquals(
            $rating->created_at->toIso8601String(),
            $actual['created'],
        );
        $this->assertEquals(
            $rating->updated_at->toIso8601String(),
            $actual['updated'],
        );


        $this->assertArrayHasKey('links', $actual);
        $this->assertCount(1, $actual['links']);
        $authorLink = $actual['links'][0];

        $this->assertArrayHasKey('rel', $authorLink);
        $this->assertEquals('author', $authorLink['rel']);
        $this->assertArrayHasKey('href', $authorLink);
        $this->assertEquals(
            route('authors.show', ['id' => $author->id]),
            $authorLink['href']
        );
    }
}
