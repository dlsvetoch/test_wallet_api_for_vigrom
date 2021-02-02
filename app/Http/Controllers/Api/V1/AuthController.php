<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Input, Response, Log;

class AuthController extends ApiController
{
    const INVALID_CREDENTIALS = 'Invalid credentials';
    const AUTH_ERROR = 'To continue, you need to log in';

    /**
     * Register a new user.
	 * @param \App\Http\Requests\CreateUserRequest $request
	 * @return mixed
	 */
    public function register(CreateUserRequest $request)
    {
        $user = User::create([
            'email'    => $request->email,
            'name'     => $request->name,
            'password' => bcrypt($request->password),
        ]);

        return $this->sendResponse($user->getAttributes(), 'OK', 201);
    }

    /**
     * Login. Getting a token
     * 
	 * @param \App\Http\Requests\LoginRequestRequest $request
	 * @return mixed
	 */
    public function login(LoginRequest $request) 
    {
        if (Auth::check()) {
            return $this->sendError('Already logged in');
        }

		if(!Auth::attempt($request->all())) {
            return $this->sendError(self::INVALID_CREDENTIALS);
        }

        $accessToken = Auth::user()->createToken('authToken')->accessToken;

        $result = [
            'access_token' => $accessToken
        ];

        return $this->sendResponse($result, 'OK', 200);

    }

    /**
     * Login. Delete a token
     * 
	 * @return mixed
	 */
    public function logout() 
    {
		if (Auth::check()) {
            Auth::user()->token()->revoke();
        }

        return $this->sendResponse(null, 'OK', 200);

    }

    /**
     * Return auth error;
     * 
     * @return mixed
     */
    public function authError()
    {
        return $this->sendError(self::AUTH_ERROR, 401);
    }
}
