<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 random templates
        Template::factory()->count(20)->create();

        // Optionally, create specific known templates (manual examples)
        Template::create([
            'name' => 'Order Shipped - Email Notification',
            'channel' => 'email',
            'module' => 'Order',
            'content' => 'Dear {{client_name}}, your order #{{order_no}} has been shipped on {{delivery_date}}.',
            'placeholders' => ['client_name', 'order_no', 'delivery_date'],
            'owner_type' => 'App\\Models\\User',
            'owner_id' => 1, // Assuming user with ID 1 exists
        ]);

        Template::create([
            'name' => 'Delivery Update - WhatsApp',
            'channel' => 'whatsapp',
            'module' => 'Order',
            'content' => 'Hi {{client_name}}, your delivery with rider {{rider_name}} is on the way. Track your order: {{tracking_link}}.',
            'placeholders' => ['client_name', 'rider_name', 'tracking_link'],
            'owner_type' => 'App\\Models\\Vendor',
            'owner_id' => 2, // Assuming vendor with ID 2 exists
        ]);
    }
}
