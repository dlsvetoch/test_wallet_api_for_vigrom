<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiController extends Controller
{
    const ACCESS_DENIED_ERROR      = 'Access denied.';
    const AVAILABLE_FEATURES_ERROR = 'This feature is not yet available.';
    const INCORRECT_OPERATION      = 'Incorrect operation.';
    const PROPERTY_ASSIGNED_ERROR  = 'The property has already been assigned';

    /**
	 * @var Model
	 */
    protected $model;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function post(ApiRequest $request)
    {
        $data = $request->validated();
		$this->model->fill($data)->push();

		return $this->sendResponse(null, 'Created', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getById($id)
    {
        $entity = $this->model->find($entityId);

		if (!$entity) {
			return $this->sendError('Not Found', 404);
		}

		return $this->sendResponse($entity, 'OK', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function put(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
    }
}
