<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\GetWalletRequest;
use App\Http\Requests\CreateWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Http\Requests\GetTransactionSumRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\WalletChange;
use App\Models\Transaction;
use App\Models\Currency;
use App\Services\CurrencyConverter\CurrencyConverter;
use App\Exceptions\ChangeBalanceException;
use Input, Response, Log;

class WalletController extends ApiController
{
    const WALLET_NOT_FOUND = 'Wallet not found';

    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\GetWalletRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(GetWalletRequest $request)
    {
        $wallet = Wallet::getActiveWalletByUser(Auth::user());

        if (!$wallet) {
            return $this->sendError(self::WALLET_NOT_FOUND, 422);
        }

        return $this->sendResponse($wallet->getAttributes(), 'Ок', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CreateWalletRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateWalletRequest $request)
    {
        $userId = Auth::id();
        $currencyCode = $request->currency;

        if (Wallet::where('user_id', $userId)->where('status', Wallet::ACTIVE_STATUS)->first()) {
            $message = 'User already have active wallet.';

            return $this->sendError($message);
        }

        if (Wallet::where('user_id', $userId)->where('status', Wallet::INACTIVE_STATUS)->first()) {
            $message = 'User have inactive wallet. Please activate the wallet';

            return $this->sendError($message);
        }

        $currencyNumberCode = Currency::getNumberCodeByCode($currencyCode);

        $wallet = Wallet::create([
            'currency_number_code' => $currencyNumberCode,
            'user_id'              => $userId
        ]);

        return $this->sendResponse($wallet, 'Created', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function show(Wallet $wallet)
    {
        if (Auth::id() !== (int) $wallet->user_id) {
            return $this->sendError(self::ACCESS_DENIED_ERROR);
        }

        return $this->sendResponse($wallet, 'OK', 200);
    }

    /**
     * Display the balance.
     *
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function showBalance(Wallet $wallet)
    {
        if (Auth::id() !== (int) $wallet->user_id) {
            return $this->sendError(self::ACCESS_DENIED_ERROR);
        }

        $data = [
            'balance' => $wallet->balance,
        ];

        return $this->sendResponse($data, 'OK', 200);
    }

    /**
     * Show transactions sum.
     *
     * @param \App\Http\Requests\GetTransactionSumRequest $request
     * @param  \App\Models\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function showTransactionSum(GetTransactionSumRequest $request, Wallet $wallet)
    {
        if (Auth::id() !== (int) $wallet->user_id) {
            return $this->sendError(self::ACCESS_DENIED_ERROR);
        }

        $params = $request->all();
        $sum = Transaction::getTransactionSumByWallet($wallet, $params);

        return $this->sendResponse(['sum' => $sum], 'OK', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWalletRequest  $request
     * @param  \App\Models\Wallet $wallet
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        if (Auth::id() !== (int) $wallet->user_id) {
            return $this->sendError(self::ACCESS_DENIED_ERROR);
        }

        $mutableProperty = $request->get('mutable_property');
        $params = $request->get('params');

        // -------- Change balance block --------

        if ($mutableProperty === Wallet::BALANCE_PROPERTY) {
            if ((int) $wallet->status !== Wallet::ACTIVE_STATUS) {
                return $this->sendError(Transaction::INACTIVE_CHANGE_BALANCE_ERROR);
            }

            if ((float) $params['value'] <= 0) {
                return $this->sendError(self::INCORRECT_OPERATION);
            }

            $isAvailableForCredit = $params['transaction_type'] === Transaction::CREDIT_TRANSACTION &&
                in_array($params['reason'], Transaction::CREDIT_TRANSACTION_REASONS);

            $isAvailableForDebit = $params['transaction_type'] === Transaction::DEBIT_TRANSACTION &&
                in_array($params['reason'], Transaction::DEBIT_TRANSACTION_REASONS);

            if (!$isAvailableForCredit && !$isAvailableForDebit) {
                return $this->sendError(self::INCORRECT_OPERATION);
            }

            $currency = Currency::where('code', $params['currency'])->first();

            try {
                /*
                | Обязательный метод по тех. заданию. По скольку laravel имеет неявную привязку моделей,
                | параметр id заменен на \App\Models\Wallet.
                */
                $wallet = $this->changeBalance($wallet, $params['transaction_type'], $params['value'], $currency, $params['reason']);
                $wallet->save();
            } catch (ChangeBalanceException $changeBalanceException) {
                Transaction::create([
                    'transaction_type'     => $params['transaction_type'],
                    'value'                => $params['value'],
                    'reason'               => $params['reason'],
                    'currency_number_code' => $wallet->currency_number_code,
                    'status_code'          => Transaction::FAILED_STATUS,
                    'status_description'   => $changeBalanceException->getMessage(),
                    'wallet_id'            => $wallet->id
                ]);

                WalletChange::create([
                    'mutable_property' => Wallet::BALANCE_PROPERTY,
                    'is_transaction'   => true,
                    'successfully'     => false,
                    'description'      => 'Attempt to change balance. Insufficient funds',
                    'wallet_id'        => $wallet->id,
                    'transaction_id'   => $transaction->id,
                ]);

                return $this->sendError(Transaction::INSUFFICIENT_FUNDS_ERROR);
            }

            $description = $params['transaction_type'] === Transaction::DEBIT_TRANSACTION ?
                Transaction::SUCCESSFUL_DEBIT_TRANSACTION :
                Transaction::SUCCESSFUL_CREDIT_TRANSACTION;


            $transaction = Transaction::create([
                'transaction_type'     => $params['transaction_type'],
                'value'                => $params['value'],
                'reason'               => $params['reason'],
                'currency_number_code' => $wallet->currency_number_code,
                'status_code'          => Transaction::SUCCESSED_STATUS,
                'status_description'   => $description,
                'wallet_id'            => $wallet->id
            ]);

            $walletChange = WalletChange::create([
                'mutable_property' => Wallet::BALANCE_PROPERTY,
                'is_transaction'   => true,
                'successfully'     => true,
                'wallet_id'        => $wallet->id,
                'transaction_id'   => $transaction->id,
            ]);

            return $this->sendResponse(['wallet_change' => $walletChange], 'Changed', 200);
        }

        // -------- Change currency block --------

        if ($mutableProperty === Wallet::CURRENCY_PROPERTY) {
            $oldCurrency = Currency::where('number_code', $wallet->currency_number_code)->first();
            $newCurrency = Currency::where('code', $params['change_to'])->first();

            if ($newCurrency->code === $oldCurrency->code) {
                return $this->sendError(self::PROPERTY_ASSIGNED_ERROR);
            }

            $wallet->balance = $this->convertWalletCurrency($wallet, $newCurrency, $wallet->balance);
            $wallet->currency_number_code = $newCurrency->number_code;

            $wallet->save();

            $walletChange = WalletChange::create([
                'mutable_property' => Wallet::CURRENCY_PROPERTY,
                'is_transaction'   => false,
                'description'      => 'Change currency from ' . $oldCurrency->code . ' to ' . $newCurrency->code,
                'successfully'     => true,
                'wallet_id'        => $wallet->id,
            ]);

            return $this->sendResponse(['wallet_change' => $walletChange], 'Changed', 200);
        }

        // -------- Change status block --------

        if ($mutableProperty === Wallet::STATUS_PROPERTY) {

            $oldStatus = $wallet->status;
            $newStatus = (int) $params['change_to'];

            if ((int) $oldStatus === $newStatus) {
                return $this->sendError(self::PROPERTY_ASSIGNED_ERROR);
            }

            $walletChange = WalletChange::create([
                'mutable_property' => Wallet::CURRENCY_PROPERTY,
                'is_transaction'   => false,
                'description'      => 'Change status from ' . $oldStatus . ' to ' . $newStatus,
                'successfully'     => true,
                'wallet_id'        => $wallet->id,
            ]);

            return $this->sendResponse(['wallet_change' => $walletChange], 'Changed', 200);
        }
    }

    /**
     * Change balance of the wallet
     *
     * @param \App\Models\Wallet $wallet
     * @param string $transactionType
     * @param float $value
     * @param \App\Models\Currency $currency
     * @param string $reason
     * @return \App\Models\Wallet $wallet
     */
    protected function changeBalance(Wallet $wallet, string $transactionType, float $value, Currency $currency, string $reason)
	{
        $value = $this->convertWalletCurrency($wallet, $currency, $value);
        $wallet->changeBalance($transactionType, $value);
        $wallet->save();

		return $wallet;
    }

    /**
     * Convert request value to wallet currency
     *
     * @param \App\Models\Wallet $wallet
     * @param \App\Models\Currency $currency
     * @param float $value
     * @return float $value
     */
    protected function convertWalletCurrency(Wallet $wallet, Currency $currency, float $value)
    {
        if ($wallet->currency->code === Currency::CURRENCY_IN_RUB && $currency->code === Currency::CURRENCY_IN_USD) {
            $value = CurrencyConverter::convertUSDToRUB($value);
		}

		if ($wallet->currency->code === Currency::CURRENCY_IN_USD && $currency->code === Currency::CURRENCY_IN_RUB) {
			$value = CurrencyConverter::convertRUBToUSD($value);
        }

        return $value;
    }
}
