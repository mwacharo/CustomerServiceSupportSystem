<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Country;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_name'          => $this->faker->word() . ' ' . $this->faker->word(),
            'country_specific_sku' => strtoupper(Str::random(12)),
            'bar_code'              => $this->faker->ean13(),
            'description'           => $this->faker->paragraph(),
            'category_id'           => Category::inRandomOrder()->first()?->id,
            'vendor_id'             => Vendor::inRandomOrder()->first()?->id,
            'country_id'            => Country::inRandomOrder()->first()?->id,
            'user_id'               => 1, // Or use auth()->id() if available
            'product_type'          => $this->faker->randomElement(['physical', 'digital']),
            'weight'                => $this->faker->randomFloat(2, 0.1, 50),
            'length'                => $this->faker->randomFloat(2, 1, 100),
            'width'                 => $this->faker->randomFloat(2, 1, 100),
            'height'                => $this->faker->randomFloat(2, 1, 100),
            'value'                 => $this->faker->randomFloat(2, 10, 500),
            'price'                 => $this->faker->randomFloat(2, 10, 1000),
            'discount_price'       => $this->faker->randomFloat(2, 5, 800),
            'tax_rate'              => $this->faker->randomFloat(2, 0, 25),
            'brand'                 => $this->faker->company(),
            'product_link'          => $this->faker->url(),
            'image_urls'            => json_encode([$this->faker->imageUrl(), $this->faker->imageUrl()]),
            'video_urls'            => json_encode([$this->faker->url()]),
            'active'                => $this->faker->boolean(90),
            'stock_management'      => $this->faker->boolean(80),
            'stock_quantity'        => $this->faker->numberBetween(1, 1000),
            'tracking_required'     => $this->faker->boolean(),
            'fragile'               => $this->faker->boolean(),
            'hazardous'             => $this->faker->boolean(),
            'temperature_sensitive' => $this->faker->boolean(),
            'returnable'            => $this->faker->boolean(),
            'packaging_type'        => $this->faker->word(),
            'handling_instructions' => $this->faker->sentence(),
            'delivery_time_window'  => $this->faker->randomElement(['morning', 'afternoon', 'evening']),
            'customs_info'          => $this->faker->paragraph(),
            'insurance_value'       => $this->faker->randomFloat(2, 0, 500),
            'ratings'               => $this->faker->numberBetween(1, 5),
            'reviews'               => $this->faker->paragraph(),
            'tags'                  => json_encode([$this->faker->word(), $this->faker->word()]),
            'slug' => Str::slug($this->faker->unique()->word()),
            'meta_title'            => $this->faker->sentence(),
            'meta_description'      => $this->faker->paragraph(),
            'update_comment'        => $this->faker->sentence(),
        ];
    }

}
