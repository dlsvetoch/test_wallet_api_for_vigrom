<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $allowedTransactionTypes = Transaction::ALLOWED_TRANSACTION_TYPES;
        $transactionType = $allowedTransactionTypes[array_rand($allowedTransactionTypes)];
        $transactionReasons = $transactionType === Transaction::DEBIT_TRANSACTION ?
            Transaction::DEBIT_TRANSACTION_REASONS :
            Transaction::CREDIT_TRANSACTION_REASONS;
        $transactionReason = $transactionReasons[array_rand($transactionReasons)];
        $statusDescription = $transactionType === Transaction::DEBIT_TRANSACTION ?
            Transaction::SUCCESSFUL_DEBIT_TRANSACTION :
            Transaction::SUCCESSFUL_CREDIT_TRANSACTION;
        $date = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'transaction_type' => $transactionType,
            'value' => rand(1, 10000),
            'currency_number_code' => 810,
            'reason' => $transactionReason,
            'status_code' => 0,
            'status_description' => $statusDescription,
            'wallet_id' => 1,
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
