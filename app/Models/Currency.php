<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    const CURRENCY_IN_RUB = 'RUB';
    const CURRENCY_IN_USD = 'USD';

	const ALLOWED_CURRENCIES = [
		self::CURRENCY_IN_RUB,
		self::CURRENCY_IN_USD
	];

    protected $table = 'currencies';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $attributes = [
        'currency' => '',
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'number_code',
        'currency',
        'is_active',
    ];

    public static function getNumberCodeByCode(string $code)
    {
        $currency = self::where('code', $code)->first();

        return $currency->number_code;
    }
}
