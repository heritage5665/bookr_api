<?php

namespace Tests\App\Exceptions;

use TestCase;
use Mockery as m;
use App\Exceptions\Handler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Exception;
//use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;



class HandlerTest extends TestCase
{
    function testItRespondsWithHtnlWhenJsonIsNotAccepted()
    {
        // make the mock a partial, you wan to mock `isDebugMode method
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldNotReceive('isDebugMode');

        //mock the interaction with Request

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(false);
        $request->shouldReceive('expectsJson')->andReturn(false);

        //mock the interaction with the exception
        $exception = m::mock(\Exception::class, ['Error!']);
        $exception->shouldNotReceive('getStatusCode');
        $exception->shouldNotReceive('getTrace');
        $exception->shouldNotReceive('getMessage');

        //call the method under test, this is not a mock method.
        $result = $subject->render($request, $exception);

        //Assert that render does not return a jsonresponse
        $this->assertNotInstanceOf(JsonResponse::class, $result);
    }

    public function testItRespondsWithJsonForJsonConsumers()
    {
        $subject  = m::mock(Handler::class)->makePartial();
        $subject
            ->shouldReceive('isDebugMode')
            ->andReturn(false);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);
        $request->shouldReceive('expectsJson')->andReturn(true);


        $exception = m::mock(\Exception::class, ['Doh!']);
        $exception->shouldReceive('getMessage')
            ->andReturn("Doh!");

        /**@var JsonResponse $result*/
        $result = $subject->render($request, $exception);
        $data = $result->getData();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertObjectHasAttribute('error', $data);
        //        $this->assertAttributeEquals('Doh!', 'message', $data->error);
        // $this->assertAttributeEquals('400', 'status', $data->error);
    }

    public function testItProvidesJsonResponsesForHttpExceptions()
    {
        $subject  = m::mock(Handler::class)->makePartial();
        $subject
            ->shouldReceive('isDebugMode')
            ->andReturn(false);


        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);
        $request->shouldReceive('expectsJson')->andReturn(true);

        $examples = [
            [
                'mock' => NotFoundHttpException::class,
                'status' => 404,
                'message' => 'Not Found'
            ],
            [
                'mock' => AccessDeniedHttpException::class,
                'status' => 403,
                'message' => 'Forbidden'
            ],
            [
                'mock' => ModelNotFoundException::class,
                'status' => 404,
                'message' => 'Not Found'
            ],
        ];
        foreach ($examples as $e) {
            $exception = m::mock($e['mock']);
            $exception->shouldReceive('getMessage')->andReturn(null);
            $exception->shouldReceive('getStatusCode')->andReturn($e['status']);

            /** @var JsonResponse $result */
            $result = $subject->render($request, $exception);
            $data = $result->getData();

            $this->assertEquals($e['status'], $result->getStatusCode());
            // $this->assertEquals($e['message'], $data->error->message);
            //$this->assertEquals($e['status'], $data->error->status);
        }
    }
}
