<?php

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    use MockeryPHPUnitIntegration;
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
    /**
     * See if the response has a header
     *
     * @param $header
     * @return $this
     */
    public function seeHasHeader($header)
    {
        $this->assertTrue(
            $this->response->headers->has($header),
            "Response should have the {$header} but does not."
        );
        return $this;
    }

    /**
     * Assert that the response header matches a given regular expression
     *
     * @param $regexp
     * @param $header
     *
     * @return $this
     */
    public function seeHasHeaderWithRegExp($header, $regexp)
    {
        $this->seeHasHeader($header)
            ->assertRegExp(
                $regexp,
                $this->response->headers->get($header)
            );
        return $this;
    }

    protected function bookFactory($count = 1)
    {
        $author = factory(\App\Author::class)->create();
        $books = factory(\App\Book::class, $count)->make();


        $books->each(function ($book) use ($author) {
            $book->author()->associate($author);
            $book->save();
        });

        if ($count === 1) {
            foreach ($books as $book) return $book;
        }

        return $books;
    }

    protected function bundleFactory($bookCount = 2)
    {
        if ($bookCount <= 1) {
            throw new \RuntimeException('A bundle must have two or more bundle');
        }

        $bundle = \factory(\App\Bundle::class)->create();
        $books = $this->bookFactory($bookCount);

        $books->each(function ($book) use ($bundle) {
            $bundle->books()->attach($book);
        });
        return $bundle;
    }
}
