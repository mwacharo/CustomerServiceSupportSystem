<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Country;
use App\Models\Branch;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'phone_number'      => $this->faker->unique()->numerify('07########'),
            'alt_phone_number'  => $this->faker->unique()->numerify('01########'),
            'name'              => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'address'           => $this->faker->address,
            'city'              => $this->faker->city,
            'state'             => $this->faker->state,
            'zip'               => $this->faker->postcode,
            'zip_code'          => $this->faker->postcode,
            'country_id'        => Country::inRandomOrder()->first()?->id,
            'branch_id'         => Branch::inRandomOrder()->first()?->id,
            'notes'             => $this->faker->sentence,
            'vendor_id'         => Vendor::inRandomOrder()->first()?->id,
            'status'            => $this->faker->randomElement(['active', 'inactive']),
            'user_id'           => User::inRandomOrder()->first()?->id,
        ];
    }
}
