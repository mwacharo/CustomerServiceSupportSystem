<?php

namespace Database\Seeders;

use App\Models\IvrOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IvrOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //



        $ivrOptions = [
            ['option_number' => 1, 'description' => 'Replacement', 'forward_number' => '254707709370'],
            ['option_number' => 2, 'description' => 'Order Follow-up', 'forward_number' => '254798765432'],
            ['option_number' => 3, 'description' => 'Refund', 'forward_number' => '254711223344'],
            ['option_number' => 4, 'description' => 'Business Prospect', 'forward_number' => '254755667788'],
            ['option_number' => 5, 'description' => 'HR', 'forward_number' => '254722334455'],
            ['option_number' => 6, 'description' => 'Speak to an Agent', 'forward_number' => null], // Assign dynamically
        ];

        foreach ($ivrOptions as $option) {
            IvrOption::create($option);
        }
    }
}
