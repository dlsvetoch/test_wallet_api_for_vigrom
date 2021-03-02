<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Currency;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');

        $this->user = User::find(1);

        Passport::actingAs(
            $this->user,
            ['create-servers']
        );

        $this->wallet = Wallet::getActiveWalletByUser($this->user);
    }

    /**
     * Geting wallets test.
     *
     * @return void
     */
    public function testGetWallets()
    {
        $this->get('/api/v1/wallets')
            ->assertStatus(200);
    }

    /**
     * Geting wallet test.
     *
     * @return void
     */
    public function testGetWallet()
    {
        $this->get('/api/v1/wallets/' . $this->wallet->id)
            ->assertStatus(200);

        $this->get('/api/v1/wallets/2')
            ->assertStatus(404);

        $this->createNewUserAndLogin();
        $this->get('/api/v1/wallets/' . $this->wallet->id)
            ->assertStatus(400);
    }

    /**
     * Geting wallet balance test.
     *
     * @return void
     */
    public function testGetWalletBalance()
    {
        $this->get('/api/v1/wallets/' . $this->wallet->id . '/balance')
            ->assertStatus(200);

        $this->get('/api/v1/wallets/2/balance')
            ->assertStatus(404);

        $this->createNewUserAndLogin();
        $this->get('/api/v1/wallets/1/balance')
            ->assertStatus(400);
    }

    /**
     * Geting wallet sum not owned by the user test.
     *
     * @return void
     */
    public function testGetWalletTransactionSum()
    {
        $this->get('/api/v1/wallets/' . $this->wallet->id . '/transactions/sum')
            ->assertStatus(200);

        $this->get('/api/v1/wallets/' . $this->wallet->id . '/transactions/sum?interval=week')
            ->assertStatus(200);

        $response = $this->get('/api/v1/wallets/2/transactions/sum?interval=week');
        $response->assertStatus(404);

        $this->createNewUserAndLogin();
        $response = $this->get('/api/v1/wallets/' . $this->wallet->id . '/transactions/sum?interval=week');
        $response->assertStatus(400);
    }

    /**
     * Geting wallet sum not owned by the user test.
     *
     * @return void
     */
    public function testCreateNewWallet()
    {
        $this->createNewUserAndLogin();

        $this->json('POST', 'api/v1/wallets', ['currency' => 'RUB'])
            ->assertStatus(201);

        Passport::actingAs(
            $this->user,
            ['create-servers']
        );

        $this->json('POST', 'api/v1/wallets', ['currency' => 'RUB'])
            ->assertStatus(400);
    }

    /**
     * Change wallet balance test test.
     *
     * @return void
     */
    public function testChangeWalletBalance()
    {
        /* Positive test for replenishing the wallet */
        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'params'           => [
                'transaction_type' => 'debit',
                'value'            => '1000',
                'currency'         => 'RUB',
                'reason'           => 'refill'
            ],
        ]);
        $response->assertStatus(200);

        /* Positive test for replenishing the wallet in USD */
        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'params'           => [
                'transaction_type' => 'credit',
                'value'            => '10',
                'currency'         => 'USD',
                'reason'           => 'payment'
            ],
        ]);
        $response->assertStatus(200);

        /* An attempt to withdraw more funds than the wallet has */
        $this->wallet->balance = 0;
        $this->wallet->save();

        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'params'           => [
                'transaction_type' => 'credit',
                'value'            => '100',
                'currency'         => 'RUB',
                'reason'           => 'payment'
            ],
        ]);
        $response->assertStatus(400);

        /* Invalid value. Should not be less than zero */
        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'params'           => [
                'transaction_type' => 'debit',
                'value'            => '-1',
                'currency'         => 'RUB',
                'reason'           => 'refill'
            ],
        ]);
        $response->assertStatus(400);

        /* Invalid combination of transaction type and reason */
        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'params'           => [
                'transaction_type' => 'debit',
                'value'            => '1',
                'currency'         => 'RUB',
                'reason'           => 'payment'
            ],
        ]);
        $response->assertStatus(400);

        /* An attempt to change the balance of an inactive wallet */
        $this->wallet->status = Wallet::INACTIVE_STATUS;
        $this->wallet->save();

        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::BALANCE_PROPERTY,
            'params'           => [
                'transaction_type' => 'credit',
                'value'            => '100',
                'currency'         => 'RUB',
                'reason'           => 'payment'
            ],
        ]);
        $response->assertStatus(400);
    }

    /**
     * Geting wallet sum not owned by the user test.
     *
     * @return void
     */
    public function testChangeWalletStatus()
    {
        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::STATUS_PROPERTY,
            'params'           => [
                'change_to' => Wallet::INACTIVE_STATUS,
            ],
        ]);
        $response->assertStatus(200);

        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::STATUS_PROPERTY,
            'params'           => [
                'change_to' => Wallet::ACTIVE_STATUS,
            ],
        ]);
        $response->assertStatus(400);
    }

    /**
     * Geting wallet sum not owned by the user test.
     *
     * @return void
     */
    public function testChangeWalletCurrency()
    {
        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::CURRENCY_PROPERTY,
            'params'           => [
                'change_to' => Currency::CURRENCY_IN_USD,
            ],
        ]);
        $response->assertStatus(200);

        $response = $this->json('PUT', 'api/v1/wallets/' . $this->wallet->id, [
            'mutable_property' => Wallet::CURRENCY_PROPERTY,
            'params'           => [
                'change_to' => Currency::CURRENCY_IN_USD,
            ],
        ]);

        $response->assertStatus(400);

    }

    /**
     * Create new use with token access.
     *
     * @return \App\Models\User
     */
    protected function createNewUserAndLogin()
    {
        $user = User::factory()->create();
        Passport::actingAs(
            $user,
            ['create-servers']
        );

        return $user;
    }
}
