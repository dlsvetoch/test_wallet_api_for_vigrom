<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $currencies = [
            [
                'code' => 'USD',
                'number_code' => 840,
                'currency' => 'United States dollar',
                'is_active' => true
            ],
            [
                'code' => 'RUB',
                'number_code' => 810,
                'currency' => 'Russian ruble',
                'is_active' => true
            ]
        ];

        DB::table('currencies')->insert($currencies);
    }
}
