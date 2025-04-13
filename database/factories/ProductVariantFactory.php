<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $color = $this->faker->safeColorName();
        $size = $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL']);

        return [
            'product_id'    => Product::factory(),
            'variant_name'  => ucfirst($color) . ' - ' . $size,
            'sku'           => strtoupper($this->faker->bothify('SKU-###??')),
            'price'         => $this->faker->randomFloat(2, 500, 5000),
            'stock'         => $this->faker->numberBetween(0, 100),
            'attributes'    => [
                'color' => $color,
                'size'  => $size,
            ],
            'image'         => $this->faker->imageUrl(400, 400, 'fashion', true),
            'is_active'     => $this->faker->boolean(90), // 90% chance it's active

            // Dimensions and weight (for shipping)
            'weight'        => $this->faker->randomFloat(2, 0.2, 5.0),   // kg
            'length'        => $this->faker->randomFloat(1, 10, 100),   // cm
            'width'         => $this->faker->randomFloat(1, 10, 100),   // cm
            'height'        => $this->faker->randomFloat(1, 1, 50),     // cm
        ];
    }
}
