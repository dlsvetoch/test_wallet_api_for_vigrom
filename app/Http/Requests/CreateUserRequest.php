<?php

namespace App\Http\Requests;

class CreateUserRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
	    return [
            'email'                 => 'required|string|min:2|max:64|unique:users|email',
            'name'                  => 'required|string|min:2|max:64',
            'password'              => 'required|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'required|string|min:6|max:32|'
	    ];
    }
}
