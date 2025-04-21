<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChannelCredential;

class ChannelCredentialSeeder extends Seeder
{
    public function run(): void
    {
        ChannelCredential::factory()->count(10)->create();

     
    }
}
