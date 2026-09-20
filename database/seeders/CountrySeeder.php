<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['id' => 1, 'name' => 'Russia', 'active' => 1],
            ['id' => 2, 'name' => 'Turkey', 'active' => 1],
            ['id' => 3, 'name' => 'Hungary', 'active' => 1],
            ['id' => 4, 'name' => 'Oman', 'active' => 1],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['name' => $country['name']],
                [
                    'id' => $country['id'],
                    'active' => $country['active']
                ]
            );
        }
    }
}
