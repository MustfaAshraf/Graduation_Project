<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegulationFileController extends Controller
{
    public function UploadFile(Request $request) {
        try {
            $request->validate([
                'file' => 'required|mimes:pdf|max:5120', // Only allow PDF files, max 5MB
                'role' => 'required|in:1,2,3,4,5', // Ensure role is one of the predefined values
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
    
            // Determine the correct column based on role
            $columnMap = [
                '1' => 'regulation',
                '2' => 'lectures_tables',
                '3' => 'academic_guide',
                '4' => 'teams_guide',
                '5' => 'postgraduate_guide',
            ];
    
            // Create new entry with dynamic column assignment
            Regulation::create([
                $columnMap[$request->role] => $fileName,
                'role' => $request->role,
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

    public function getLatestFile(Request $request) {
        $request->validate([
            'role' => 'required|in:1,2,3,4,5', // Ensure role is valid
        ]);
    
        // Map role to the corresponding column
        $columnMap = [
            '1' => 'regulation',
            '2' => 'lectures_tables',
            '3' => 'academic_guide',
            '4' => 'teams_guide',
            '5' => 'postgraduate_guide',
        ];
    
        $column = $columnMap[$request->role];
    
        // Get the latest record where the selected column is not null
        $latestFile = Regulation::whereNotNull($column)
                                ->orderBy('created_at', 'desc')
                                ->first();
    
        if (!$latestFile || empty($latestFile->$column)) {
            return response()->json([
                'msg' => 'No files found for the selected role',
                'file' => null
            ], 200);
        }
    
        return response()->json([
            'msg' => 'File retrieved successfully',
            'file' => url('Files/' . $latestFile->$column),
            'uploaded_at' => $latestFile->created_at,
        ], 200);
    }

}
