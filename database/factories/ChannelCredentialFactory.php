<?php

namespace Database\Factories;

use App\Models\ChannelCredential;
use App\Models\User; // Assuming you are associating it with a User model
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelCredentialFactory extends Factory
{
    protected $model = ChannelCredential::class;

    public function definition(): array
    {
        return [
            'channel' => $this->faker->randomElement(['email', 'sms', 'whatsapp', 'facebook', 'telegram']),
            // 'provider' => $this->faker->user,
            'api_key' => $this->faker->uuid,
            'api_secret' => $this->faker->sha256,
            'access_token' => $this->faker->sha256,
            'access_token_secret' => $this->faker->sha256,
            'auth_token' => $this->faker->sha256,
            'client_id' => $this->faker->uuid,
            'client_secret' => $this->faker->sha1,
            'user_name' => $this->faker->userName,
            'password' => $this->faker->password,
            'account_sid' => 'AC' . $this->faker->numerify('########################'),
            'account_id' => $this->faker->uuid,
            'app_id' => $this->faker->uuid,
            'app_secret' => $this->faker->sha1,
            'page_access_token' => $this->faker->sha256,
            'page_id' => $this->faker->numerify('##########'),
            'phone_number' => $this->faker->e164PhoneNumber,
            'email_address' => $this->faker->safeEmail,
            'webhook' => $this->faker->url,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'value' => $this->faker->text(100),
            'description' => $this->faker->sentence,
            'meta' => [
                'region' => $this->faker->country,
                'verified' => true,
                'expires_at' => now()->addDays(30)->toDateTimeString(),
            ],
            // Polymorphic fields
            'credentialable_id' => User::factory(), // This will create a user for each credential
            'credentialable_type' => User::class, // Adjust based on the model you want to associate with
        ];
    }
}
