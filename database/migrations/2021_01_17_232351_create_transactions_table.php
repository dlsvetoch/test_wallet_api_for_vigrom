<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('transaction_type', 16);
            $table->decimal('value', 20, 4);
            $table->unsignedSmallInteger('currency_number_code');
            $table->string('reason', 16);
            $table->unsignedTinyInteger('status_code');
            $table->text('status_description');
            $table->unsignedBigInteger('wallet_id');
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction');
    }
}
