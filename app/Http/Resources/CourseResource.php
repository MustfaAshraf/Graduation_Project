<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image ? url('courses_imgs/' . $this->image) : null,
            'instructor' => $this->instructor,
            'price' => $this->price,
            'Rating Numbers' => $this->ratings_count,
            'rating' => $this->ratings_count > 0 
                        ? round($this->ratings_sum / $this->ratings_count, 2) 
                        : 0
        ];
    }

}
