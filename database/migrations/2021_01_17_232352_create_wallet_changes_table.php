<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_changes', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('mutable_property', 16);
            $table->boolean('is_transaction');
            $table->string('description', 64)->nullable();
            $table->boolean('successfully');
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
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
        Schema::dropIfExists('wallet_changes');
    }
}
