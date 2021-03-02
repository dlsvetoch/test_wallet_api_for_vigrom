<?php

namespace App\Services\CurrencyConverter;

use App\Services\CurrencyConverter\Recipients\IRecipient;
use Response;

class CurrencyConverter
{
    private $recipient;

    public function __construct(IRecipient $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * Convert rubles to dollars
     *
     * @param float
     * @return float
     */
    public function convertRUBToUSD(float $value)
    {

        $exchangeRates = $this->recipient->getExchangeRates();
        $rates = (float) $this->changeFractionalFormatToGeneral($exchangeRates[840]['Value']);
        $result = $value / $rates;

        return round($result, 4);
    }

    /**
     * Convert dollars to rubles
     *
     * @param float
     * @return float
     */
    public function convertUSDToRUB(float $value)
    {
        $exchangeRates = $this->recipient->getExchangeRates();
        $rates = (float) $this->changeFractionalFormatToGeneral($exchangeRates[840]['Value']);
        $result = $value * $rates;

        return round($result, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Changes the Russian format of fractional numbers to the generally accepted
     *
     * @param string
     * @return float
     */
    public function changeFractionalFormatToGeneral(string $value)
    {
        return str_replace(',', '.', $value);
    }
}
