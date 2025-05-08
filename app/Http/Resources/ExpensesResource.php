<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpensesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            '1st' => $this->first_term ? url($this->first_term) : null,
            '2nd' => $this->second_term ? url($this->second_term) : null,
            '3rd' => $this->third_term ? url($this->third_term) : null,
            '4th' => $this->fourth_term ? url($this->fourth_term) : null,
            '5th' => $this->fifth_term ? url($this->fifth_term) : null,
            '6th' => $this->sixth_term ? url($this->sixth_term) : null,
            '7th' => $this->seventh_term ? url($this->seventh_term) : null,
            '8th' => $this->eighth_term ? url($this->eighth_term) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
