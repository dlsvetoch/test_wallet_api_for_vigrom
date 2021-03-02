<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallets', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('currency_number_code')->references('number_code')->on('currencies');
        });

        Schema::table('transactions', function($table) {
            $table->foreign('wallet_id')->references('id')->on('wallets');
            $table->foreign('currency_number_code')->references('number_code')->on('currencies');
        });

        Schema::table('wallet_changes', function($table) {
            $table->foreign('wallet_id')->references('id')->on('wallets');
            $table->foreign('transaction_id')->references('id')->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foreign_key');
    }
}
