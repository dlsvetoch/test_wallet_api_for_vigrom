<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Wallet;
use Input, Response, Log;

class TransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $wallet = Wallet::getActiveWalletByUser(Auth::user());
        $transactions = Transaction::getTransactionsByWallet($wallet);

        return $this->sendResponse($transactions, 'Ok', 200);
    }
}