<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $channels = ['whatsapp', 'email', 'sms', 'telegram'];
        $modules = ['Order', 'Client', 'Delivery', 'Support', 'Billing'];

        return [
            'name' => $this->faker->sentence(3), // e.g., "Order Confirmation Email"
            'channel' => $this->faker->randomElement($channels),
            'module' => $this->faker->randomElement($modules),
            'content' => 'Hello {{client_name}}, your order {{order_no}} is being processed!',
            'placeholders' => ['client_name', 'order_no'],
            'owner_type' => 'App\\Models\\User', // Default owner (you can customize)
            'owner_id' => \App\Models\User::factory(), // Assuming a User factory exists
        ];
    }
}
