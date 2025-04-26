<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'channel'     => $this->channel,
            'module'      => $this->module,
            'name'        => $this->name,
            'subject'     => $this->subject,
            'content'     => $this->content,
            'placeholders'=> $this->placeholders,
            'owner'       => [
                'id'   => $this->owner_id,
                'type' => $this->owner_type,
                'name' => optional($this->owner)->name,
            ],
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
