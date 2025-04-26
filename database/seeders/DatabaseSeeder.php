<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

                // Example: $this->call(UserSeeder::class);
        // $this->call(IvrOptionSeeder::class);
        $this->call(CountrySeeder::class);
        // $this->call(BranchSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(VendorSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(ProductVariantSeeder::class);
        $this->call(ClientSeeder::class,);
        $this->call(OrderSeeder::class,);

        $this->call(OrderItemSeeder::class,);

        $this->call(ChannelCredentialSeeder::class,);





    }
}
