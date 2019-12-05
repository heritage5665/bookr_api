<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BookControllerValidationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function testItValidatesRequiredFieldsWhenCreatingANewBook()
    {
        $this->post('/books', [], ['Accept' => 'application/json']);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode());

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('description', $body);
        //$this->assertArrayHasKey('author', $body);

        $this->assertEquals(['The title field is required.'], $body['title']);
        $this->assertEquals(['The description field is required.'], $body['description']);
        //$this->assertEquals(['The author field is required.'], $body['author']);
    }


    /**
     * @test
     */
    public function testItValidatesPassedFieldsWhenUpdatingABook()
    {
        $book = $this->bookFactory();


        $this->put("/books/{$book->id}", [], ['Accept' => 'application/json']);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode());

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('description', $body);
        // $this->assertArrayHasKey('author', $body);

        $this->assertEquals(['The title field is required.'], $body['title']);
        $this->assertEquals(['The description field is required.'], $body['description']);
        //$this->assertEquals(['The author field is required.'], $body['author']);
    }

    public function testTitleFailsCreateValidationWhenJustTooLong()
    {
        //creating a book
        $book = $this->bookFactory();
        $book->title = str_repeat('a', 256);

        $this->post("/books", [
            'title' => $book->title,
            'description' => $book->description,
            'author_id' => $book->author->id
        ], ['Accept' => 'application/json']);

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'title' => ["The title may not be greater than 255 characters."]
            ])
            ->notSeeInDatabase('books', ['title' => $book->title]);
    }

    public function testTitleFailsUpdateValidationWhenJustTooLong()
    {
        //updating a book
        $book = $this->bookFactory();
        $book->title = str_repeat('a', 256);

        $this->put("/books/{$book->id}", [
            'title' => $book->title,
            'description' => $book->description,
            'author_id' => $book->author->id,
        ], ['Accept' => 'application/json']);

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'title' => ["The title may not be greater than 255 characters."]
            ])
            ->notSeeInDatabase('books', ['title' => $book->title]);
    }

    public function testTitlePassesCreateValidationWhenExactlyMax()
    {
        //creating a book
        $book = $this->bookFactory();
        $book->title = str_repeat('a', 255);

        $this->post("/books", [
            'title' => $book->title,
            'description' => $book->description,
            'author_id' => $book->author->id
        ], ['Accept' => 'application/json']);

        $this->seeStatusCode(Response::HTTP_CREATED)
            ->seeInDatabase('books', ['title' => $book->title]);
    }

    public function testTitlePassesUpdateValidationWhenExactlyMax()
    {
        //updating a book
        $book = $this->bookFactory();
        $this->put("/books/{$book->id}", [
            'title' => $book->title,
            'description' => $book->description,
            'author_id' => $book->author->id
        ], ['Accept' => 'application/json']);

        $this->seeStatusCode(Response::HTTP_OK)

            ->seeInDatabase('books', ['title' => $book->title]);
    }
}
