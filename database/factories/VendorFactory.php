<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   


     public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'company_name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'billing_email' => $this->faker->optional()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'alt_phone' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zip_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'region' => $this->faker->state(),
            'warehouse_location' => $this->faker->streetAddress(),
            'preferred_pickup_time' => $this->faker->time('H:i'),

            'contact_person_name' => $this->faker->name(),
            'business_type' => $this->faker->randomElement(['Retailer', 'Wholesaler', 'Manufacturer']),
            'registration_number' => strtoupper($this->faker->bothify('REG####')),
            'tax_id' => strtoupper($this->faker->bothify('TIN####')),

            'website_url' => $this->faker->optional()->url(),
            'social_media_links' => json_encode([
                'facebook' => $this->faker->url(),
                'twitter' => $this->faker->url(),
                'linkedin' => $this->faker->url(),
            ]),
            'bank_account_info' => json_encode([
                'bank' => $this->faker->company . ' Bank',
                'account_name' => $this->faker->name(),
                'account_number' => $this->faker->bankAccountNumber(),
                'branch' => $this->faker->city(),
            ]),

            'delivery_mode' => $this->faker->randomElement(['pickup', 'delivery', 'both']),
            'payment_terms' => $this->faker->randomElement(['Net 7', 'Net 15', 'Net 30']),
            'credit_limit' => $this->faker->randomFloat(2, 0, 500000),
            'integration_id' => $this->faker->uuid(),

            'onboarding_stage' => $this->faker->randomElement(['pending', 'active', 'verified']),
            'last_active_at' => $this->faker->optional()->dateTimeThisMonth(),

            // 'branch_id' => Branch::inRandomOrder()->first()?->id ?? null,
            'rating' => $this->faker->randomFloat(1, 3, 5),
            'status' => $this->faker->boolean(85),
            'notes' => $this->faker->optional()->sentence(),

            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
