<?php

namespace App\Http\Requests;

use App\Models\Transaction;

class GetTransactionSumRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $allowedReasons = implode(',', Transaction::DEBIT_TRANSACTION_REASONS);

	    return [
            'interval' => 'in:week',
            'reason'   => 'in:' . $allowedReasons
	    ];
    }
}