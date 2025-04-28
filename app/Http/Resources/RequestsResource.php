<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type_en' => $this->type_en,
            'type_ar' => $this->type_ar,
            'status' => $this->status,
            'message_en' => $this->message_en,
            'message_ar' => $this->message_ar,
            'completed_at' => $this->completed_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role,
            ],
        ];
    }
}
