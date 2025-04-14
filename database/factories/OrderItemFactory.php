<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price' => $this->faker->randomFloat(2, 5, 100), // Random price between 5 and 100
            'total_price' => $this->faker->randomFloat(2, 10, 500), // Random total price between 10 and 500
            'quantity' => $this->faker->numberBetween(1, 10), // Random quantity between 1 and 10
            'sku_no' => $this->faker->unique()->word, // Random unique SKU number
            'quantity_sent' => $this->faker->numberBetween(0, 10),
            'quantity_delivered' => $this->faker->numberBetween(0, 10),
            'quantity_returned' => $this->faker->numberBetween(0, 5),
            'quantity_remaining' => $this->faker->numberBetween(0, 10),
            'shipped' => $this->faker->boolean,
            'sent' => $this->faker->boolean,
            'delivered' => $this->faker->boolean,
            'returned' => $this->faker->boolean,
            'product_rate' => $this->faker->randomFloat(2, 5, 50), // Random product rate between 5 and 50
            'quantity_tobe_delivered' => $this->faker->numberBetween(0, 10),
            'product_id' => Product::inRandomOrder()->first()->id ?? Product::factory(), // Use an existing product or create one if none exist
            'sku_id' => $this->faker->randomDigitNotNull, // Random digit for SKU ID, can replace with actual SKU model relation
            'order_id' => Order::factory(), // Assuming the Order model has a factory
            'seller_id' => Vendor::factory(), // Assuming the Seller model has a factory
        ];
    }
}
