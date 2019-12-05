<?php

namespace Test\App\Http\Controllers;


use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use TestCase;

class AuthorsControllerTest extends TestCase
{
    use DatabaseMigrations;



    /**@test */
    public function testIndexRespondsWith200StatusCode()
    {
        $this->get("/authors")->seeStatusCode(Response::HTTP_OK);
    }

    public function getValidationTestData()
    {

        $author = \factory(\App\Author::class)->create();

        return [
            [
                //create
                'method' => 'post',
                'url' => '/authors',
                'data' => [
                    'name' => 'Joe Jeryy',
                    'gender' => 'male',
                    'biography' => 'An anonymous author'
                ]
            ],
            [
                //create
                'method' => 'put',
                'url' => "/authors/{$author->id}",
                'data' => [
                    'name' => $author->name,
                    'gender' => $author->gender,
                    'biography' => $author->biography
                ]
            ]

        ];
    }

    public function testIndexShouldReturnACollectionOfRecords()
    {
        $authors = factory(\App\Author::class, 2)->create();

        $this->get('/authors', ['Accept' => 'applicaation/json']);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(2, $body['data']);

        foreach ($authors as $author) {
            $this->seeJson([
                'id' => $author->id,
                'name' => $author->name,
                'gender' => $author->gender,
                'biography' => $author->biography,
                'created' => $author->created_at->toIso8601String(),
                'updated' => $author->updated_at->toIso8601String(),
            ]);
        }
    }

    public function testShowShouldReturnAValidAuthor()
    {
        $book =  $this->bookFactory();
        $author = $book->author;

        $this->get("/authors/{$author->id}", ['Accept' => 'application/json']);
        $body = json_decode($this->response->getContent(), true);


        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
            'gender' => $author->gender,
            'biography' => $author->biography,
            'created' => $author->created_at->toIso8601String(),
            'updated' => $author->updated_at->toIso8601String(),
        ]);
    }

    public function testShowShouldFailOnAnIvalidAuthor()
    {
        $this->get('/authors/1234', ['Accept' => 'application/json']);
        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this->seeJson([
            'message' => 'Not Found',
            'status' => Response::HTTP_NOT_FOUND
        ]);

        $body  = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('error', $body);
        $error = $body['error'];

        $this->assertEquals('Not Found', $error['message']);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $error['status']);
    }

    public function testShowOptimallyIncludesBooks()
    {
        $book = $this->bookFactory();
        $author = $book->author;

        $this->get(
            "/authors/{$author->id}?include=books",
            ['Accept' => 'application/json']
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('data', $data['books']);
        $this->assertCount(1, $data['books']['data']);

        //see author data
        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
        ]);

        //test included data (the first record)
        $actual = $data['books']['data'][0];
        $this->assertEquals($book->id, $actual['id']);
        $this->assertEquals($book->title, $actual['title']);
        $this->assertEquals($book->description, $actual['description']);
        $this->assertEquals(
            $book->created_at->toIso8601String(),
            $actual['created']
        );
        $this->assertEquals(
            $book->updated_at->toIso8601String(),
            $actual['updated']
        );
    }

    public function testStoreCanCreateANewStore()
    {
        $postData = [
            'name' => 'H.G Wells',
            'gender' => 'male',
            'biography' => 'prolific Science-Fiction Writer'
        ];

        $this->post('/authors', $postData, ['Accept' => 'application/json']);

        $this->seeStatusCode(201);
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson($postData);

        $this->seeInDatabase('authors', $postData);
    }

    public function testValidationValidateRequiredField()
    {
        $author = \factory(\App\Author::class)->create();
        $tests = [
            ['method' => 'post', 'url' => '/authors'],
            ['method' => 'put', 'url' => "/authors/{$author->id}"],
        ];

        foreach ($tests as $test) {
            $method = $test['method'];
            $this->{$method}($test['url'], [], ['Accept' => 'application/json'])
                ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            $data = $this->response->getData(true);

            $fields = ['name', 'gender', 'biography'];


            foreach ($fields as $field) {
                $this->assertArrayHasKey($field, $data);
                $this->assertEquals(["The {$field} field is required."], $data[$field]);
            }
        }
    }

    public function testValidationInValidatesIncorrectGenderData()
    {

        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['gender'] = 'unknown';
            $this->{$method}($test['url'], $test['data'], ['Accept' => 'application/json'])
                ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);


            $data = $this->response->getData(true);
            $this->assertCount(1, $data);
            $this->assertArrayHasKey('gender', $data);
            $this->assertEquals(
                ["Gender format is invalid: must equal 'male' or 'female' "],
                $data['gender']
            );
        }
    }


    public function testValidationInvalidatesWhenNameIsJustTooLong()
    {


        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['name'] = \str_repeat('aa', 200);
            $this->{$method}($test['url'], $test['data'], ['Accept' => 'application/json'])
                ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);


            $data = $this->response->getData(true);
            $this->assertCount(1, $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertEquals(
                ["The name may not be greater than 255 characters."],
                $data['name']
            );
        }
    }

    public function testValidationValidatesWhenNameIsJustLongEnough()
    {
        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['name'] = \str_repeat('a', 200);
            $this->{$method}($test['url'], $test['data'], ['Accept' => 'application/json'])
                ->seeStatusCode(Response::HTTP_CREATED);


            $this->seeInDatabase('authors', $test['data']);
        }
    }

    public function testStoreReturnsAValidLocationHeader()
    {
        $postData = [
            'name' => 'Adegbite Yusuf',
            'gender' => 'male',
            'biography' => 'An prolific Computer Science writer'
        ];
        $this->post('/authors', $postData, ['Accept' => 'application/json']);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        //check the location header

        $id = $data['data']['id'];
        $this->seeHasHeaderWithRegExp('Location', "#/authors/{$id}$#");
    }

    public function testUpdateCanUpdateExistingAuthor()
    {
        $author = factory(\App\Author::class)->create();
        $requestData = [
            'name' => 'New Author Name',
            'gender' => 'male',
            'biography' => 'A prolific Computer Science writer'
        ];
        $this
            ->put(
                "/authors/{$author->id}",
                $requestData,
                ['Accept' => 'application/json']
            )
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeJson($requestData)
            ->seeInDatabase('authors', [
                'name' => 'New Author Name',
            ])
            ->notSeeInDatabase(
                'authors',
                ['name' => $author->name]
            );

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function testDeleteCanRemoveAnAuthorAndHisOrBooks()
    {
        $author = \factory(\App\Author::class)->create();

        $this->delete("/authors/{$author->id}")
            ->seeStatusCode(Response::HTTP_NO_CONTENT)
            ->notSeeInDatabase('authors', ['id' => $author->id])
            ->notSeeInDatabase('books', ['author_id' => $author->id]);
    }

    public function testDeletingAnInvalidAuthorShouldReturnA404()
    {
        $this->delete('/authors/99999999', [], ['Accept' => 'application/jsom'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND);
    }
}
