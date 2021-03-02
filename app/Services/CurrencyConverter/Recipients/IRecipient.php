<?php


namespace App\Services\CurrencyConverter\Recipients;


interface IRecipient
{
    public function getExchangeRates(): array;
}
