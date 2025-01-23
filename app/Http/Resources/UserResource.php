<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'semester' => $this->semester,
            'department' => $this->department,
            'gpa' => $this->gpa,
            'university_id' => $this->university_id,
            'national_id' => $this->national_id,
            'image' => $this->image ? url('images/' . $this->image) : null,
            'otp' => $this->otp_code,
            'is_verified' => $this->is_verified
        ];
    }
}
