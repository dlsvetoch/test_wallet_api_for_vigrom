<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Input, Response, Log;

class UserController extends ApiController
{
    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        if (Auth::id() !== $user->id) {
            return $this->sendError(self::ACCESS_DENIED_ERROR);
        }

        return $this->sendResponse($user->getAttributes(), 'Changed', 200);
    }
}
