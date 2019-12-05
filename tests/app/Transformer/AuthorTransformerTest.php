<?php

namespace Test\App\Transformer;

use TestCase;
use League\Fractal\TransformerAbstract;
use App\Transformer\AuthorTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;
use League\Fractal\Resource\Collection;

class AuthorTransformerTest extends TestCase
{
    use DatabaseMigrations;

    /**test */
    public function testItCanInitialized()
    {
        $subject = new AuthorTransformer();
        $this->assertInstanceOf(TransformerAbstract::class, $subject);
    }

    public function testItCanTransformRelatedBooks()
    {
        $subject = new AuthorTransformer();
        $book = $this->bookFactory();
        $author = $book->author;

        $data  = $subject->includeBooks($author);
        $this->assertInstanceOf(Collection::class, $data);
    }
    public function testItCanTransformAnAuthor()
    {
        $author = factory(\App\Author::class)->create();
        $subject = new AuthorTransformer();
        $actual = $subject->transform($author);

        $this->assertEquals($author->id, $actual['id']);
        $this->assertEquals($author->name, $actual['name']);
        $this->assertEquals($author->gender, $actual['gender']);
        $this->assertEquals($author->biography, $actual['biography']);
        $this->assertEquals(
            $author->created_at->toIso8601String(),
            $actual['created']
        );
        $this->assertEquals(
            $author->updated_at->toIso8601String(),
            $actual['updated']
        );
    }
}
