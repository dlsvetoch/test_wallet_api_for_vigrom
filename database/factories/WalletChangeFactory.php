<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletChange;

class WalletChangeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WalletChange::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'is_transaction' => true,
            'successfully' => true,
            'wallet_id' => Wallet::factory(),
            'transaction_id' => Transaction::factory(),
        ];
    }
}