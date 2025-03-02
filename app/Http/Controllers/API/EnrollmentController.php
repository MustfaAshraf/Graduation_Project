<?php

namespace App\Http\Controllers\API\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {


        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'national_id' => 'required|string|max:255',
            'id_photo_f' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_photo_b' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nomination_card_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Store the uploaded files
        if ($request->hasFile('id_photo_f')) {
            $img = $request->file('id_photo_f');
            $imgName = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('enrollments/id_photo');
            $img->move($destinationPath, $imgName);

            // Update user image and URL
            Enrollment::create(['id_photo_f' => $imgName]);
            $imgUrl_front = url('enrollments/id_photo/' . $imgName);
        }

        if ($request->hasFile('id_photo_b')) {
            $img = $request->file(key: 'id_photo_b');
            $imgName = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('enrollments/id_photo');
            $img->move($destinationPath, $imgName);

            // Update user image and URL
            Enrollment::create(['id_photo_b' => $imgName]);
            $imgUrl_back = url('enrollments/id_photo/' . $imgName);
        }

        if ($request->hasFile('nomination_card_photo')) {
            $img = $request->file('nomination_card_photo');
            $imgName = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('enrollments/nomination_photo');
            $img->move($destinationPath, $imgName);

            // Update user image and URL
            Enrollment::create(['nomination_card_photo' => $imgName]);
            $imgUrl_nomination = url('images/' . $imgName);
        }

        // Create the enrollment record
        $enrollment = Enrollment::create([
            'name' => $validatedData['name'],
            'national_id' => $validatedData['national_id'],
        ]);

        // Return the response with URLs for the uploaded images
        return response()->json([
            'msg' => 'Request submitted successfully',
            'data' => [
                'name' => $enrollment->name,
                'national_id' => $enrollment->national_id,
                'id_photo_f' => $imgUrl_front,
                'id_photo_b' => $imgUrl_back,
                'nomination_card_photo' => $imgUrl_nomination,
            ]
        ], 200);
    }
}