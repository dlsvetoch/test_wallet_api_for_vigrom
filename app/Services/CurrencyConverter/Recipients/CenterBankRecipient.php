<?php


namespace App\Services\CurrencyConverter\Recipients;


use Illuminate\Support\Facades\Http;

class CenterBankRecipient implements IRecipient
{
    /**
     * Receives currency rates in real time
     *
     * @return mixed
     */
    public function getExchangeRates(): array {
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
}
