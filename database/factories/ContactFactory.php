<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            // 'contactable_id' => null, // set manually when attaching
            // 'contactable_type' => null, // set manually when attaching
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->e164PhoneNumber(),
            'alt_phone' => $this->faker->optional()->e164PhoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'zip_code' => $this->faker->postcode(),
            // 'country_id' => null, // assign if you already have countries seeded
            // 'state_id' => null,   // assign if you already have states seeded
            'country_name' => $this->faker->country(),
            'state_name' => $this->faker->state(),
            'user_id' => User::factory()->create()->id,
            // 'type' => $this->faker->randomElement(['customer', 'vendor', 'employee', 'partner']),
            'company_name' => $this->faker->company(),
            'job_title' => $this->faker->jobTitle(),
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
            'tags' => json_encode($this->faker->randomElements(['priority', 'new', 'loyal', 'vip'], rand(1, 2))),
            'profile_picture' => null,
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->boolean(90),
        ];
    }
}
