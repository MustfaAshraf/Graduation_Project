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
        try{
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed', // Custom key
                'errors' => $e->errors(), // Validation errors
            ], 422);
        }

        if($request->hasFile('image')) {

            $img = $request->file('image'); 
            $imgName = rand() . time() . "." . $img->extension(); 
            $destinationPath = public_path('courses_imgs'); 
            $img->move($destinationPath, $imgName);

            CourseImages::create([
                'image' => $imgName,
            ]);

            return response()->json([
                'msg' => 'Image stored successfully',
                'image' => $imgName,
            ], 200);
        } else {
            return response()->json([
                'msg' => 'No image found in the request',
            ], 404);
        }
    }

    public function getImages()
    {
        $courseImages = CourseImages::all();

        if ($courseImages->isEmpty()) {
            return response()->json([
                'msg' => 'No images found'
            ], 402);
        }else{
            return response()->json([
                'msg' => 'Images retrieved successfully',
                'data' => $courseImages
            ], 200);
        }
    }
}
