<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Branch;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Kenya', 'Uganda', 'Zambia', 'Tanzania', 'Nigeria',
                'Ghana', 'South Africa', 'Ethiopia', 'Rwanda', 'Sudan'
            ]),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zip_code' => $this->faker->postcode(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'user_id' => 1, // You can change this if needed
        ];
    }
}
