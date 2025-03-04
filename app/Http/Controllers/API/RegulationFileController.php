<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegulationFileController extends Controller
{
    public function UploadFile(Request $request){
        try{
        $request->validate([
            'file' => 'required|mimes:pdf|max:5120', // Only allow PDF files, max 5MB
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        // Check if file is uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file'); 
            $fileName = rand() . time() . "_rules" . "." . $file->extension(); 
            $destinationPath = public_path('Files'); 
            $file->move($destinationPath, $fileName);

            Regulation::create([
                'file' => $fileName,
            ]);

            $fileUrl = url('Files/' . $fileName);

            return response()->json([
                'msg' => 'File Uploaded successfully',
                'file' => $fileUrl,
            ], 200);
        } else {
            return response()->json([
                'msg' => 'No files found in the request',
            ], 451);
        }
    }

    public function getLatestFile()
    {
        $latestFile = Regulation::orderBy('created_at', 'desc')->first();

        if (!$latestFile) {
            return response()->json([
                'msg' => 'No regulation files found',
                'file' => []
            ], 200);
        }

        return response()->json([
            'msg' => 'File retrieved successfully',
            'file' => url('Files/' . $latestFile->file),
            'uploaded_at' => $latestFile->created_at,
        ], 200);
    }

}
