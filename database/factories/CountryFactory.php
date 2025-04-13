<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Country;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country(),
            'code' => strtoupper($this->faker->lexify('??')),
            'phone_code' => '+' . $this->faker->numberBetween(1, 999),
            'status' => $this->faker->boolean(), // true or false


        ];
    }
}
