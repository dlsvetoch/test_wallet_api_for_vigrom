<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletChange;
use App\Models\Transaction;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Faker\Factory as FakerFactory;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $wallet = Wallet::factory()
            ->for($user)
            ->create();

        $transactions = Transaction::factory()
        ->count(100)
        ->for($wallet)
        ->create();

        foreach ($transactions as $transaction) {
            $walletChanges = WalletChange::factory()
            ->for($wallet)
            ->for($transaction)
            ->create([
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at
            ]);
        }
    }
}
