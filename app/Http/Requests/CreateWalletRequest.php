<?php

namespace App\Http\Requests;

use App\Models\Currency;

class CreateWalletRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $allowedCurrencies = implode(',', Currency::ALLOWED_CURRENCIES);
	    return [
            'currency' => 'required|in:' . $allowedCurrencies,
	    ];
    }
}