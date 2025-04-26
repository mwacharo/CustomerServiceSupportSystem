<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly pick a contactable type
        $contactableTypes = [
            User::class,
            // Add other models later like Vendor::class, Rider::class, Driver::class, Company::class, etc
        ];

        $contactableType = $this->faker->randomElement($contactableTypes);
        $contactable = $contactableType::factory()->create(); // Create the owner model

        return [
            'contactable_id' =>User::factory(),
            'contactable_type' => User::class,
            'name' => $this->faker->name(),
            'email' => $this->faker->optional()->safeEmail(),
            'phone' => $this->faker->optional()->e164PhoneNumber(),
            'alt_phone' => $this->faker->optional()->e164PhoneNumber(),
            'address' => $this->faker->optional()->address(),
            'city' => $this->faker->optional()->city(),
            'zip_code' => $this->faker->optional()->postcode(),
            'country_name' => $this->faker->optional()->country(),
            'state_name' => $this->faker->optional()->state(),
            'type' => $this->faker->randomElement(['customer', 'vendor', 'employee', 'partner']),
            'company_name' => $this->faker->optional()->company(),
            'job_title' => $this->faker->optional()->jobTitle(),
            'whatsapp' => $this->faker->optional()->e164PhoneNumber(),
            'linkedin' => $this->faker->optional()->url(),
            'telegram' => $this->faker->optional()->userName(),
            'facebook' => $this->faker->optional()->url(),
            'twitter' => $this->faker->optional()->url(),
            'instagram' => $this->faker->optional()->url(),
            'wechat' => $this->faker->optional()->userName(),
            'snapchat' => $this->faker->optional()->userName(),
            'tiktok' => $this->faker->optional()->userName(),
            'youtube' => $this->faker->optional()->url(),
            'pinterest' => $this->faker->optional()->url(),
            'reddit' => $this->faker->optional()->userName(),
            'consent_to_contact' => $this->faker->boolean(80),
            'consent_given_at' => now(),
        
            'profile_picture' => null,
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->boolean(90),
        ];
    }
}
