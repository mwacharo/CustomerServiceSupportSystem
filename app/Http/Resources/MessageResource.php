<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'recipient_phone' => $this->recipient_phone,
            'status' => $this->status,
            'message_status' => $this->message_status,
            'sent_at' => optional($this->sent_at)->format('F d, Y h:i A'),
            'created_at' => optional($this->created_at)->format('F d, Y h:i A'),
        ];
    }
    
}
