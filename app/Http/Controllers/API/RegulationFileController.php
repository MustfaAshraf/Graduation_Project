<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Regulation;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegulationFileController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function UploadFile(Request $request) {
        try {
            $request->validate([
                'file' => 'required|mimes:pdf|max:5120', // Only allow PDF files, max 5MB
                'role' => 'required|in:1,2,3,4,5,6,7,8', // Ensure role is one of the predefined values
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
                '6' => 'ai_regulation',
                '7' => 'cybersecurity_regulation',
                '8' => 'medical_regulation',
            ];
    
            // Create new entry with dynamic column assignment
            Regulation::create([
                $columnMap[$request->role] => $fileName,
                'role' => $request->role,
            ]);
    
            $fileUrl = url('Files/' . $fileName);
            
            // 🟢 Notification content
            $title = 'تم تحديث لائحة';
            $title_en = 'A regulation file has been updated';
            $body = 'يرجي مراجعة اللوائح المحدثة';
            $body_en = 'Please check the updated regulations';

            // 🔔 Send notification to all users
            $deviceTokens = User::whereNotNull('device_token')->pluck('device_token')->unique();

            foreach ($deviceTokens as $token) {
                try {
                    $this->firebaseService->sendNotification(
                        $token,
                        $title,
                        $body,
                        [
                            'file_url' => $fileUrl
                            ]
                    );

                    // Update notification with English & type info
                    Notification::where('device_token', $token)->latest()->first()?->update([
                        'title_en' => $title_en,
                        'body_en' => $body_en,
                        'type' => '3'
                    ]);

                } catch (\Throwable $e) {
                    Log::warning("Failed to send regulation notification to token: $token | Error: " . $e->getMessage());
                }
            }

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
            'role' => 'required|in:1,2,3,4,5,6,7,8', // Ensure role is valid
        ]);
    
        // Map role to the corresponding column
        $columnMap = [
            '1' => 'regulation',
            '2' => 'lectures_tables',
            '3' => 'academic_guide',
            '4' => 'teams_guide',
            '5' => 'postgraduate_guide',
            '6' => 'ai_regulation',
            '7' => 'cybersecurity_regulation',
            '8' => 'medical_regulation',
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
    public function getAllFiles(){
        $files = Regulation::all();

        if ($files->isEmpty()) {
            return response()->json([
                'msg' => 'No files found',
                'data' => []
            ], 200);
        }

        $fileUrls = $files->map(function ($file) {
            return [
                'role' => $file->role,
                'regulation' => $file->regulation ? url('Files/' . $file->regulation) : null,
                'lectures_tables' => $file->lectures_tables ? url('Files/' . $file->lectures_tables) : null,
                'academic_guide' => $file->academic_guide ? url('Files/' . $file->academic_guide) : null,
                'teams_guide' => $file->teams_guide ? url('Files/' . $file->teams_guide) : null,
                'postgraduate_guide' => $file->postgraduate_guide ? url('Files/' . $file->postgraduate_guide) : null,
                'ai_regulation' => $file->ai_regulation ? url('Files/' . $file->ai_regulation) : null,
                'cybersecurity_regulation' => $file->cybersecurity_regulation ? url('Files/' . $file->cybersecurity_regulation) : null,
                'medical_regulation' => $file->medical_regulation ? url('Files/' . $file->medical_regulation) : null,
            ];
        });

        return response()->json([
            'msg' => 'All files retrieved successfully',
            'data' => $fileUrls
        ], 200);
    }

}
