<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Country;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference'         => strtoupper(Str::random(10)),
            'drawer_id'         => null,
            'client_id'         => Client::inRandomOrder()->first()?->id,
            'agent_id'          => User::inRandomOrder()->first()?->id,
            'total_price'       => $this->faker->randomFloat(2, 100, 2000),
            'invoice_value'     => $this->faker->randomFloat(2, 50, 1500),
            'amount_paid'       => $this->faker->randomFloat(2, 0, 2000),
            'sub_total'         => $this->faker->randomFloat(2, 50, 1500),
            'order_no'          => 'ORD-' . $this->faker->unique()->numerify('###'),
            'sku_no'            => strtoupper(Str::random(8)),
            'tracking_no'       => strtoupper(Str::random(12)),
            'waybill_no'        => strtoupper(Str::random(8)),
            'customer_notes'    => $this->faker->sentence,
            'discount'          => $this->faker->randomFloat(2, 0, 100),
            'shipping_charges'  => $this->faker->randomFloat(2, 0, 200),
            'charges'           => $this->faker->randomFloat(2, 0, 100),
            'delivery_date'     => $this->faker->dateTimeBetween('now', '+7 days'),
            'delivery_d'        => $this->faker->dateTimeBetween('now', '+10 days'),
            'status'            => 'Inprogress',
            'delivery_status'   => 'Inprogress',
            'warehouse_id'      => Branch::inRandomOrder()->first()?->id,
            'country_id'        => Country::inRandomOrder()->first()?->id,
            'vendor_id'         => Vendor::inRandomOrder()->first()?->id,
            'payment_method'    => $this->faker->randomElement(['mpesa', 'cash', 'card']),
            'mpesa_code'        => 'MP' . strtoupper(Str::random(8)),
            'platform'          => 'upload',
            'pickup_address'    => $this->faker->address,
            'pickup_phone'      => $this->faker->phoneNumber,
            'pickup_shop'       => $this->faker->company,
            'pickup_city'       => $this->faker->city,
            'user_id'           => User::inRandomOrder()->first()?->id,
            // 'rider_id'          => Rider::inRandomOrder()->first()?->id,
            'distance'          => $this->faker->randomFloat(2, 1, 50),
            'weight'            => $this->faker->randomFloat(2, 0.1, 20),
            'order_date'        => $this->faker->dateTimeBetween('-1 week', 'now'),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
