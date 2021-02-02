<?php

namespace App\Http\Requests;

use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Transaction;

class UpdateWalletRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $mutableProperty = $this->request->get('mutable_property');

        $allowedMutableProperties = implode(',', Wallet::ALLOWED_MUTABLE_PROPERTIES);
        $allowedTransactionTypes = implode(',', Transaction::ALLOWED_TRANSACTION_TYPES);
        $allowedCurrencies = implode(',', Currency::ALLOWED_CURRENCIES);
        $allowedReasons = implode(',', Transaction::ALLOWED_REASONS);

        switch($mutableProperty) {
            case Wallet::BALANCE_PROPERTY:
                $validationRules = [
                    'params.transaction_type' => 'required|in:' . $allowedTransactionTypes,
                    'params.value'            => 'required|numeric',
                    'params.currency'         => 'required|in:' . $allowedCurrencies,
                    'params.reason'           => 'required|in:' . $allowedReasons
                ];
                break;

            case  Wallet::CURRENCY_PROPERTY:
                $validationRules = [
                    'params.change_to' => 'required|in:' . $allowedCurrencies
                ];   
                break;

            case Wallet::STATUS_PROPERTY:
                $validationRules = [
                    'params.change_to' => 'required|integer|max:2'
                ]; 
                break;

            default: 
                $validationRules = [];
        }

        return array_merge(
            ['mutable_property' => 'required|string|in:' . $allowedMutableProperties],
            $validationRules
        );
    }
}