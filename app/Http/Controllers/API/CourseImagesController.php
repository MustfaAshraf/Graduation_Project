<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseImages;
use Illuminate\Support\Facades\Storage;

class CourseImagesController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $imgName = rand() . time() . "." . $img->extension();
            $imagePath = $img->storeAs('courses_images', $imgName, 'public');

            $courseImage = CourseImages::create([
                'image' => $imagePath,
            ]);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'image_url' => asset('storage/' . $imagePath),
            ], 200);
        } else {
            return response()->json([
                'message' => 'No image found in the request',
            ], 400);
        }
    }

    public function getImages()
    {
        $courseImages = CourseImages::all();

        if ($courseImages->isEmpty()) {
            return response()->json(['message' => 'No images found'], 404);
        }

        $imageUrls = $courseImages->map(function($image) {
            return asset('storage/' . $image->image);
        });

        return response()->json([
            'images' => $imageUrls,
        ]);
    }
}
