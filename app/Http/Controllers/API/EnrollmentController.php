<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        try {
            $enrollments = Enrollment::all()->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'name' => $enrollment->name,
                    'national_id' => $enrollment->national_id,
                    'id_photo_f' => url('enrollments/id_photo/' . $enrollment->id_photo_f),
                    'id_photo_b' => url('enrollments/id_photo/' . $enrollment->id_photo_b),
                    'nomination_card_photo' => url('images/' . $enrollment->nomination_card_photo),
                    'created_at' => $enrollment->created_at,
                ];
            });

            if ($enrollments->isEmpty()) {
                return response()->json([
                    'msg' => 'No enrollment requests found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'msg' => 'Enrollment requests retrieved successfully',
                'data' => $enrollments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => $e->getMessage()
            ], 422);
        }
    }

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
            $imgName_front = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('enrollments/id_photo');
            $img->move($destinationPath, $imgName_front);
            $imgUrl_front = url('enrollments/id_photo/' . $imgName_front);
        }

        if ($request->hasFile('id_photo_b')) {
            $img = $request->file(key: 'id_photo_b');
            $imgName_back = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('enrollments/id_photo');
            $img->move($destinationPath, $imgName_back);
            $imgUrl_back = url('enrollments/id_photo/' . $imgName_back);
        }

        if ($request->hasFile('nomination_card_photo')) {
            $img = $request->file('nomination_card_photo');
            $imgName_nomination = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('enrollments/nomination_photo');
            $img->move($destinationPath, $imgName_nomination);
            $imgUrl_nomination = url('images/' . $imgName_nomination);
        }

        // Create the enrollment record
        $enrollment = Enrollment::create([
            'name' => $validatedData['name'],
            'national_id' => $validatedData['national_id'],
            'id_photo_f' => $imgName_front,
            'id_photo_b' => $imgName_back,
            'nomination_card_photo' => $imgName_nomination,
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
