<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
            'description_en' => $this->description_en,
            'title_ar' => $this->title_ar,
            'description_ar' => $this->description_ar,
            'image' => $this->image ? url('courses_imgs/' . $this->image) : null,
            'instructor_en' => $this->instructor_en,
            'instructor_description_en' => $this->instructor_description_en,
            'instructor_ar' => $this->instructor_ar,
            'instructor_description_ar' => $this->instructor_description_ar,
            'price' => $this->price,
            'ratings_count' => $this->ratings_count,
            'rating' => $this->ratings_count > 0 
                        ? round($this->ratings_sum / $this->ratings_count, 2) 
                        : 0,
        ];
    }

}
