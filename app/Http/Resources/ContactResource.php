<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'alt_phone' => $this->alt_phone,
            'address' => $this->address,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
            'country_name' => $this->country_name,
            'state_name' => $this->state_name,
            'type' => $this->type,
            'company_name' => $this->company_name,
            'job_title' => $this->job_title,
            'social_links' => [
                'whatsapp' => $this->whatsapp,
                'linkedin' => $this->linkedin,
                'telegram' => $this->telegram,
                'facebook' => $this->facebook,
                'twitter' => $this->twitter,
                'instagram' => $this->instagram,
                'wechat' => $this->wechat,
                'snapchat' => $this->snapchat,
                'tiktok' => $this->tiktok,
                'youtube' => $this->youtube,
                'pinterest' => $this->pinterest,
                'reddit' => $this->reddit,
            ],
            'consent_to_contact' => $this->consent_to_contact,
            'tags' => $this->tags,
            'profile_picture' => $this->profile_picture,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
