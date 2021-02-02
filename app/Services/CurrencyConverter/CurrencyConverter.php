<?php

namespace App\Services\CurrencyConverter;

use Response;
use Illuminate\Support\Facades\Http;

class CurrencyConverter
{
	/**
     * Receives currency rates in real time
     * 
	 * @return mixed
	 */
	public static function getExchangeRates() {
        $currentDate = new \DateTime();
        $date = $currentDate->format('d/m/Y');
        $response = Http::get('http://www.cbr.ru/scripts/XML_daily.asp', [
            'date_req' => $date,
        ]);

        $xml = simplexml_load_string($response->body());
        $json = json_encode($xml);
        $currenciesArray = json_decode($json,TRUE);

        $result = [];

        foreach ($currenciesArray['Valute'] as $key => $value) {
            $result[$value['NumCode']] = [
                'CharCode' => $value['CharCode'],
                'Value'    => $value['Value'],
            ];
        }
        
        return $result;  
    }
    
    /**
     * Convert rubles to dollars
     * 
     * @param float
     * @return float
     */
    public static function convertRUBToUSD(float $value) 
    {
        
        $exchangeRates = self::getExchangeRates();
        $rates = (float) self::changeFractionalFormatToGeneral($exchangeRates[840]['Value']);
        $result = $value / $rates;
        
        return round($result, 4);
    }

    /**
     * Convert dollars to rubles
     * 
     * @param float
     * @return float
     */
    public static function convertUSDToRUB(float $value) 
    {
        $exchangeRates = self::getExchangeRates();
        $rates = (float) self::changeFractionalFormatToGeneral($exchangeRates[840]['Value']);
        $result = $value * $rates;
        
        return round($result, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Changes the Russian format of fractional numbers to the generally accepted
     * 
     * @param string
     * @return float
     */
    public static function changeFractionalFormatToGeneral(string $value)
    {
        return str_replace(',', '.', $value);
    }
}