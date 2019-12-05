<?php

namespace Tests\App\Http\Controllers;

use Illuminate\Http\Response;
use TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthorRatingsControllerTest
{
    use DatabaseMigrations;

    public function test_store_can_add_a_rating_to_an_author()
    {
        $author = factory(\App\Author::class)->create();

        $this->post(
            "/authors/{$author->id}/ratings",
            ['value' => 5],
            ['Accept' => 'application/json']
        )->seeStatusCode(Response::HTTP_CREATED)
            ->seeJson([
                'value' => 5
            ])
            ->seeJson([
                'rel' => 'author',
                'href' => route('author.show', ['id' => $author->id])
            ]);

        $body = $this->response->getData(true);
        $this->assertArrayHasKey('data', $body);
        $this->assertArratHasKey('links', $body['data']);
    }
}
