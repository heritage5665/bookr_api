<?php

namespace Tests\App\Http\Controllers;


use \TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Carbon\Carbon;

class BooksControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexStatusCodeShouldBe200()
    {
        $this->get('/books')->seeStatusCode(200);
    }

    public function testIndexShouldReturnACollectionOfRecords()
    {
        $books = $this->bookFactory(6);
        $this->get('/books');

        $content = \json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $content);

        foreach ($books as  $book) {
            $this->seeJson([
                'id' => $book->id,
                'title' => $book->title,
                'description' => $book->description,
                'author' => $book->author->name,
                'created' => $book->created_at->toIso8601String(),
                'updated' => $book->updated_at->toIso8601String()

            ]);
        }
    }

    public function testShowShouldReturnAValidBook()
    {
        $book = $this->bookFactory();


        $this->get("/books/{$book->id}")
            ->seeStatusCode(200);

        //get the response and assert the data key
        $content = \json_decode($this->response->getContent(), true);
        $this->assertArrayHaskey('data', $content);
        $data = $content['data'];

        //Aassert the Book properties match
        $this->assertEquals($book->id, $data['id']);
        $this->assertEquals($book->title, $data['title']);
        $this->assertEquals($book->description, $data['description']);
        $this->assertEquals($book->author->name, $data['author']);
        $this->assertEquals($book->created_at->toIso8601String(), $data['created']);
        $this->assertEquals($book->updated_at->toIso8601String(), $data['created']);
    }

    public function testShowShouldFailWhenTheBookIdDoesNotexist()
    {
        $this->get('/books/9990', ['Accept' => 'application/json'])
            ->seeStatusCode(404)
            ->seeJson([
                'message' => 'Not Found',
                'status' => Response::HTTP_NOT_FOUND

            ]);
    }
    public function testShowRouteShouldNotMatchAnInvalidRoute()
    {
        $this->get('/books/this-is-invalid');
        $this->assertNotRegExp(
            '/Book not Found/',
            $this->response->getContent(),
            'BooksController@show route matching when it should not'
        );
    }

    public function testStoreShouldSaveNewBookInTheDataBase()
    {
        $author = factory(\App\Author::class)->create([
            'name' => 'H.G. Wells'
        ]);

        $this->post('/books', [
            'title' => 'The invisible man',
            'description' => 'An invisible is trapped in the terror of his own creation',
            'author_id' => $author->id,
        ], ['Accept' => 'application/json']);

        $body = \json_decode($this->response->getContent(), true);
        // dd($body);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertEquals('The invisible man', $data['title']);
        $this->assertEquals('An invisible is trapped in the terror of his own creation', $data['description']);
        $this->assertEquals('H.G. Wells', $data['author']);
        $this->assertTrue($data['id'] > 0, 'Expected a positive integer but did not see one.');

        $this->assertArrayHasKey('updated', $data);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['updated']);
        $this->assertArrayHasKey('created', $data);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['created']);

        $this->seeInDatabase('books', ['title' => 'The invisible man']);
    }


    public function testStoreShouldRespondWith201AndLocationHeaderWhenSuccessful()
    {
        $author = factory(\App\Author::class)->create();
        $this->post('/books', [
            'title' => 'The invisible man ',
            'description' => 'An invisible is trapped in the terror of his own creation',
            'author_id' => $author->id
        ], ['Accept' => 'application/json']);
        $this->seeStatusCode(201)
            ->seeHasHeaderWithRegExp('Location', '#/books/[\d]+$#');
    }

    public function testUpdateShouldOnlyChangeFillableFields()
    {

        $book = $this->bookFactory();
        $book->title = str_repeat('a', 256);

        $this->notSeeInDatabase('books', [
            'title' => 'The War of the Worlds',
            'description' => 'A science fiction masterpiece about Martians invading London',

        ]);

        $this->put("/books/{$book->id}", [
            'id' => 5,
            'title' => 'The War of the Worlds',
            'description' => 'the book is better the movie',
        ], ['Accept' => 'application/json']);

        $this->seeStatusCode(200)
            ->seeJson([
                'id' => 1,
                'title' => 'The War of the Worlds',
                'description' => 'the book is better the movie',
            ])->seeInDatabase('books', [
                'title' => 'The War of the Worlds'
            ]);
        // verify the data key in the response

        $body = \json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);


        $data = $body['data'];

        $this->assertArrayHasKey('created', $data);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['created']);
        $this->assertArrayHasKey('updated', $data);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['updated']);
    }
    public function testUpdateShouldFailWithInvalidId()
    {
        $this->put('/books/99999999999999999')
            ->seeStatusCode(404)
            ->seeJsonEquals([
                'error' => [
                    'message' => 'Book not Found'
                ]
            ]);
    }
    public function testUpdateShouldNotMatchAnInvalidRoute()
    {
        $this->put('/books/this-is-invalid')->seeStatusCode(404);
    }
    public function testDestroyShouldRemoveAValidBook()
    {
        $book = $this->bookFactory();
        $book->title = str_repeat('a', 256);


        $this->delete("/books/{$book->id}")
            ->seeStatusCode(204)
            ->isEmpty();
        $this->notSeeInDatabase(
            'books',
            ['id' => 1]
        );
    }

    public function testDestroyShouldReturn404WithInvalidId()
    {
        $this
            ->delete('/books/999999')
            ->seeStatusCode(404)
            ->seeJsonEquals([
                'error' => [
                    'message' => 'Book not Found'
                ]
            ]);
    }
    function testDestroyShouldNotMatchAnInvalidRoute()
    {
        $this
            ->delete('/books/this-is-invalid')
            ->seeStatusCode(404);
    }
}
