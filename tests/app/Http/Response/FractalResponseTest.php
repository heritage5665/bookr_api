<?php

namespace Tests\App\Http\FractalResponse;

use TestCase;
use Mockery as m;
use League\Fractal\Manager;
use Illuminate\Http\Request;
use League\Fractal\Serializer\SerializerAbstract;
use App\Http\Response\FractalResponse;
use League\Fractal\TransformerAbstract;
use League\Fractal\Scope;

class FractalResponseTest extends TestCase
{
    /**@test */
    public function testItCanBeInitialised()
    {
        $manager = m::mock(Manager::class);
        $serializer = m::mock(SerializerAbstract::class);
        $request = m::mock(Request::class);

        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $fractal = new FractalResponse($manager, $serializer, $request);
        $this->assertInstanceOf(FractalResponse::class, $fractal);
    }

    public function testItCanTranformAnItem()
    {
        //Request
        $request = m::mock(Request::class);

        //Transformer
        $transformer = m::mock(TransformerAbstract::class);

        //Scope
        $scope = m::mock(Scope::class);
        $scope
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['foo' => 'bar']);

        //Serializer
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->andReturn($scope);
        $manager
            ->shouldReceive('createData')
            ->once()
            ->andReturn($scope);
        $subject = new FractalResponse($manager, $serializer, $request);
        $this->assertIsArray(
            $subject->item(['foo' => 'bar'], $transformer)
        );
    }

    public function testItCanTranformACollection()
    {
        $data = [
            ['foo' => 'bar'],
            ['fizz' => 'buzz']
        ];

        //Request
        $request = m::mock(Request::class);

        //Transformer
        $transformer = m::mock(TransformerAbstract::class);

        //Scope
        $scope = m::mock(Scope::class);
        $scope
            ->shouldReceive('toArray')
            ->once()
            ->andReturn($data);

        //Serializer
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->andReturn($scope);
        $manager
            ->shouldReceive('createData')
            ->once()
            ->andReturn($scope);
        $subject = new FractalResponse($manager, $serializer, $request);
        $this->assertIsArray(

            $subject->collection($data, $transformer)
        );
    }

    public function testItCanParseIncludesWhenPassed()
    {
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager->shouldReceive('setSerializer')->with($serializer);
        $manager->shouldReceive('parseIncludes')
            ->with('books')
            ->andReturn('books');

        $request = m::mock(Request::class);
        $request->shouldNotReceive('query');

        (new FractalResponse($manager, $serializer, $request));
        //->parseIncludes('book');
    }

    public function testItShouldParseRequestQueryIncludesWithNoArguments()
    {
        $serializer = m::mock(SerializerAbstract::class);

        $manager = m::mock(Manager::class);
        $manager->shouldReceive('setSerializer')->with($serializer);
        $manager
            ->shouldReceive('parseIncludes')
            ->with('books');

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('query')
            ->with('include')
            ->andReturn('books');

        (new FractalResponse($manager, $serializer, $request))->parseIncludes();
    }
}
