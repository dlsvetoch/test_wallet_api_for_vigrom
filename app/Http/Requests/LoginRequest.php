<?php

namespace App\Http\Requests;

class LoginRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
	    return [
            'email'    => 'required|string|min:2|max:64|email|exists:users',
            'password' => 'required|string|min:6|max:32|'
	    ];
    }
}