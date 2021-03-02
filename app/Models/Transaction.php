<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Wallet;

class Transaction extends Model
{
    use HasFactory;

    const FAILED_STATUS    = 0;
    const SUCCESSED_STATUS = 1;

    const CREDIT_TRANSACTION = 'credit';
	const DEBIT_TRANSACTION  = 'debit';
	
	const ALLOWED_TRANSACTION_TYPES = [
		self::CREDIT_TRANSACTION,
		self::DEBIT_TRANSACTION,
    ];
    
    const REASON_IS_REFILL  = 'refill';
    const REASON_IS_PAYMENT = 'payment';
    const REASON_IS_STOCK   = 'stock';
    const REASON_IS_REFUND  = 'refund';

    const DEBIT_TRANSACTION_REASONS = [
        self::REASON_IS_REFILL,
        self::REASON_IS_STOCK,
        self::REASON_IS_REFUND,
    ];

    const CREDIT_TRANSACTION_REASONS = [
        self::REASON_IS_PAYMENT
    ];

    const ALLOWED_REASONS = [
        self::REASON_IS_REFILL,
        self::REASON_IS_PAYMENT,
        self::REASON_IS_STOCK,
        self::REASON_IS_REFUND,
    ];

    const SUCCESSFUL_CREDIT_TRANSACTION = 'Successful credit transaction';
    const SUCCESSFUL_DEBIT_TRANSACTION  = 'Successful debit transaction';
    const CREDIT_TRANSACTION_FAILED     = 'Credit operation failed';

    const INSUFFICIENT_FUNDS_ERROR = 'Insufficient funds to pay';
    const INACTIVE_CHANGE_BALANCE_ERROR = 'You cannot change the balance of an inactive user';

    protected $fillable = [
		'transaction_type',
        'value',
        'currency_number_code',
		'reason',
        'status_code',
        'status_description',
        'wallet_id'
	];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Get transactions by wallet
     * 
     * @param \App\Models\Wallet $wallet
     * @return mixed
     */
    public static function getTransactionsByWallet(Wallet $wallet)
    {
        $model = self::query();
        $model->where('wallet_id', $wallet->id);

		return $model->get();
    }

    /**
     * Get transaction sum by Wallet
     * 
     * @param \App\Models\Wallet $wallet
     * @param array $params
     * @return float;
     */
    public static function getTransactionSumByWallet(Wallet $wallet, array $params)
    {
        /*
        | Одно из требований это SQL запрос, который вернет сумму, полученную по причине refind за последние 7 дней. 
        | В связи с тем, что laravel предоставляет мне удобный query builder в коде я сделал это через этот механизм.
        | Если же нужно написать чистый запрос в MySql, то он будет выглядить примерно так:
        | 
        | SELECT SUM(value) FROM transactions WHERE reason = 'refund' AND created_at BETWEEN CAST('$from' AS DATE) AND CAST('$to' AS DATE);
        |
        */
        $model = self::query();

        $model->where('wallet_id', $wallet->id);

        if (!empty($params['reason'])) {
            $model->where('reason', $params['reason']);
        } else {
            $model->where('transaction_type', self::DEBIT_TRANSACTION);
        }

		if (!empty($params['interval'])) {
            if ($params['interval'] === 'week') {
                $interval = [
                    Carbon::today()->subDay(7),
                    Carbon::today()
                ];
            }

            $model->whereBetween('created_at', $interval);
        }

        return $model->sum('value');
    }
}
