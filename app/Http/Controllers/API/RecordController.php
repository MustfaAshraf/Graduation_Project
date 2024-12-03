<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordResource;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;

class RecordController extends Controller
{

    public function store(Request $request)

    {

        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        $user = User::where('token', $token)->first();

        $validatedData = $request->validate([
            'payment_receipt' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if($request->hasFile('payment_receipt')) {
            $img = $request->file('payment_receipt'); 
            $imgName = rand() . time() . "." . $img->extension(); 
            $destinationPath = public_path('receipts'); 
            $img->move($destinationPath, $imgName);
        }
        $records = Record::create([
            'student_name' => $user->name,
            'academic_year' => $user->semester,
            'receipt' => $imgName
        ]);

        $data = [
            'msg' => 'Request sent successfully',
            'status' => 200,
            'data' => [
                'Student_Name' => $user->name,
                'Academic_Year' => $user->semester,
                'Receipt' => $imgName
            ]
        ];
        return response()->json($data);
    }
}
