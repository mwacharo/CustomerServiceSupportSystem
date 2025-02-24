<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;




class VoiceCallTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_make_call_to_phone()
    {
        $payload = [
            "direction" => "Outbound",
            "callerNumber" => "BoxleoKenya.Agent",
            "destinationNumber" => "+254741821113",
            "clientDialedNumber" => "+254741821113",
            "sessionId" => "TEST_SESSION_123",
            "isActive" => "1"
        ];

        $response = $this->postJson('/api/v1/africastalking-handle-callback', $payload);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<Dial record="true" phoneNumbers="+254741821113"/>');
    }
}


