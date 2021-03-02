<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ChangeBalanceException;
use App\Models\Currency;
use App\Models\Transaction;

class Wallet extends Model
{
    use HasFactory;

    const ACTIVE_STATUS   = 0;
	const INACTIVE_STATUS = 1;
	
	const ALLOWED_STATUSES = [
		self::ACTIVE_STATUS,
		self::INACTIVE_STATUS,
	];

	const BALANCE_PROPERTY  = 'balance';
	const CURRENCY_PROPERTY = 'currency';
	const STATUS_PROPERTY   = 'status';

	const ALLOWED_MUTABLE_PROPERTIES = [
		self::BALANCE_PROPERTY,
		self::CURRENCY_PROPERTY,
		self::STATUS_PROPERTY,
	];

    protected $table = 'wallets';

    protected $primaryKey = 'id';
    
    
	protected $fillable = [
		'user_id',
		'currency_number_code',
		'balance',
		'status',
	];
	
	protected $attributes = [
        'balance' => 0.0,
        'status' => self::ACTIVE_STATUS,
    ];

	public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
	}
	
	public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_number_code', 'number_code');
	}
	
	/**
     * Get active wallet by user
     * 
     * @param \App\Models\User $user
     * @return self
     */
	public static function getActiveWalletByUser(User $user)
    {
        $model = self::query();

		$model->where('user_id', $user->id)->where('status', self::ACTIVE_STATUS);

		return $model->first();
	}

	/**
     * Change balance of the wallet
     * 
     * @param string $transactionType
     * @param float $value
     * @return self
     */
    public function changeBalance(string $transactionType, float $value)
	{
		switch ($transactionType) {
			case Transaction::DEBIT_TRANSACTION:
				$result = (float) $this->balance + $value;
				$this->balance = (string) $result;
				break;

			case Transaction::CREDIT_TRANSACTION:
				if ((float) $this->balance < $value) 
					throw new ChangeBalanceException(Transaction::INSUFFICIENT_FUNDS_ERROR);
				
					$result = (float) $this->balance - $value;
					$this->balance = (string) $result;
		}

		return $this;
	}
}
