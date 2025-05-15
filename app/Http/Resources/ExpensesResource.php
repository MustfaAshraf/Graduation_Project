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
            'first_term' => $this->first_term ? url('Expenses/' . $this->first_term) : null,
            'second_term' => $this->second_term ? url('Expenses/' . $this->second_term) : null,
            'third_term' => $this->third_term ? url('Expenses/' . $this->third_term) : null,
            'fourth_term' => $this->fourth_term ? url('Expenses/' . $this->fourth_term) : null,
            'fifth_term' => $this->fifth_term ? url('Expenses/' . $this->fifth_term) : null,
            'sixth_term' => $this->sixth_term ? url('Expenses/' . $this->sixth_term) : null,
            'seventh_term' => $this->seventh_term ? url('Expenses/' . $this->seventh_term) : null,
            'eighth_term' => $this->eighth_term ? url('Expenses/' . $this->eighth_term) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
