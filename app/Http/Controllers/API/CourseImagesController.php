<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseImages;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CourseImagesController extends Controller
{
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(), // Validation errors
            ], 422);
        }

        if ($request->hasFile('image')) {
            $img = $request->file('image'); 
            $imgName = rand() . time() . "." . $img->extension(); 
            $destinationPath = public_path('courses_imgs'); 
            $img->move($destinationPath, $imgName);

            CourseImages::create([
                'image' => $imgName,
            ]);

            $imageUrl = url('courses_imgs/' . $imgName);

            return response()->json([
                'msg' => 'Image stored successfully',
                'image' => $imageUrl,
            ], 200);
        } else {
            return response()->json([
                'msg' => 'No image found in the request',
            ], 451);
        }
    }


    public function getImages()
    {
        $courseImages = CourseImages::all();

        if ($courseImages->isEmpty()) {
            return response()->json([
                'msg' => 'No images available'
            ], 200);
        } else {
            // Map the images to include URLs
            $courseImagesWithUrls = $courseImages->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image' => url('courses_imgs/' . $image->image),
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ];
            });

            return response()->json([
                'msg' => 'Images retrieved successfully',
                'data' => $courseImagesWithUrls
            ], 200);
        }
    }

}
