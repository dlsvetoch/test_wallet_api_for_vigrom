<?php

namespace App\Http\Requests;

use App\Models\Wallet;

class GetWalletRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $allowedStatuses = implode(',', Wallet::ALLOWED_STATUSES);

	    return [
		    'currency_number_code' => 'integer',
		    'status'               => 'in:' . $allowedStatuses,
		    'offset'               => 'integer',
		    'limit'                => 'integer'
	    ];
    }
}
