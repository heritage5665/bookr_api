<?php

namespace App\Http\Controllers;

use App\Author;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Transformer\AuthorTransformer;


/**
 * Class Authorscontroller
 * @package App\Http\Controllers
 */

class AuthorsController extends Controller
{
    /**
     * GET /authors
     * @return array
     */

    public function index()
    {
        //return collection of authors
        return $this->collection(
            Author::all(),
            new AuthorTransformer()
        );
    }
    /**
     * GET /author/{id}
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        //return an author item by id

        return $this->item(
            Author::findOrFail($id),
            new AuthorTransformer()
        );
    }
    /**
     * POST /authors
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        //post author data in the database
        $this->validateAuthor($request);

        $author = Author::create($request->all());

        $data = $this->item($author, new AuthorTransformer);

        return response()->json($data, 201, [
            'Location' => route('authors.show', ['id' => $author->id])
        ]);
    }

    /**
     * PUT /authors/{id}
     *
     * @param Request $request
     *
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $this->validateAuthor($request);
        $author = Author::findOrFail($id);
        $author->fill($request->all());
        $author->save();
        return response()->json(
            $this->item($author, new AuthorTransformer()),
            Response::HTTP_CREATED
        );
    }
    /**
     *
     * DELETE /authors/{id}
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        Author::findOrFail($id)->delete();
        return response(null, 204);
    }

    public function validateAuthor(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|max:255',
            'gender' => [
                'required',
                'regex:/^(male|female)$/i',
            ],
            'biography' => 'required'
        ], [
            'gender.regex' => "Gender format is invalid: must equal 'male' or 'female' "
        ]);
    }
}
