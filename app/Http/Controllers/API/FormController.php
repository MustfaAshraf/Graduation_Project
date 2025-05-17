<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
    public function uploadForm(Request $request)
    {
        try{
        $request->validate([
            'role' => 'required|in:1,2',
            'file' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $columnMap = [
            '1' => 'registration',
            '2' => 'petitions',
        ];

        $column = $columnMap[$request->role];

        // Get the first record or create a new one (only one record will be kept)
        $form = Form::firstOrNew(['role' => $request->role]);

        // Delete old file if exists
        if ($form->$column) {
            $oldPath = public_path('Forms/' . $form->$column);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Store new file
        if ($request->hasFile('file')) {
            $file = $request->file('file'); 
            $fileName = rand() . time() . "_forms" . "." . $file->extension(); 
            $destinationPath = public_path('Forms'); 
            $file->move($destinationPath, $fileName);
    
            // Create new entry with dynamic column assignment
            Form::create([
                $columnMap[$request->role] => $fileName,
                'role' => $request->role,
            ]);
    
            $formUrl = url('Forms/' . $fileName);
    
            return response()->json([
                'msg' => 'Form Uploaded successfully',
                'file' => $formUrl,
            ], 200);
        } else {
            return response()->json([
                'msg' => 'No files found in the request',
            ], 451);
        }
    }
    public function getForm(Request $request)
    {
        try{
        $request->validate([
            'role' => 'required|in:1,2',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $form = Form::where('role', $request->role)->first();

        if ($form) {
            return response()->json([
                'msg' => 'Form retrieved successfully',
                'data' => url('Forms/' . $form->{$request->role}),
            ], 200);
        } else {
            return response()->json([
                'msg' => 'No form found for the given role',
            ], 200);
        }
    }
    public function deleteForm(Request $request)
    {
        try{
        $request->validate([
            'role' => 'required|in:1,2',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $form = Form::where('role', $request->role)->first();

        if ($form) {
            // Delete the file from the server
            $filePath = public_path('Forms/' . $form->{$request->role});
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete the record from the database
            $form->delete();

            return response()->json([
                'msg' => 'Form deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'msg' => 'No form found for the given role',
            ], 200);
        }
    }
    public function getAllForms()
    {
        $forms = Form::all();

        if ($forms->isEmpty()) {
            return response()->json([
                'msg' => 'No forms found',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'msg' => 'Forms retrieved successfully',
            'data' => $forms,
        ], 200);
    }
    public function fillAndPrintForm(Request $request)
    {
        try {
            $request->validate([
                'role' => 'required|in:1', // Only registration for now
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $role = $request->role;


        if ($role == '1') {
            try{
            $request->validate([
                'student_name' => 'required|string',
                'academic_year' => 'required|string',
                'level' => 'required|string',
                'purpose' => 'required|string',
                'date' => 'required|date',
                'notes' => 'nullable|string',
            ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'msg' => $e->errors(),
                ], 422);
            }

            $form = Form::latest()->first();

            if (!$form || !$form->registration) {
                return response()->json([
                    'msg' => 'Registration form not found.'
                ], 451);
            }

            // Get full path of the registration form
            $formPath = public_path('Forms/' . $form->registration);

            if (!file_exists($formPath)) {
                return response()->json([
                    'msg' => 'Form file is missing on server.'
            ], 451);
            }

            // 🧠 Prepare command data for the script
            $command = escapeshellcmd("python scripts/fill_registration_form.py "
                . escapeshellarg($formPath) . " "
                . escapeshellarg($request->student_name) . " "
                . escapeshellarg($request->academic_year) . " "
                . escapeshellarg($request->level) . " "
                . escapeshellarg($request->purpose) . " "
                . escapeshellarg($request->date) . " "
                . escapeshellarg($request->notes ?? ''));

            // ⏯ Execute script
            exec($command . ' 2>&1', $output, $resultCode);

            if ($resultCode !== 0) {
                return response()->json([
                    'msg' => 'Failed to process and print the form.',
                    'debug' => $output
                ], 412);
            }

            return response()->json([
                'msg' => 'Form filled and sent to printer successfully.',
                'filled_form' => asset('storage/app/public/filled_forms/' . $output[0]) // Assuming the script returns the filename
        ], 200);
        }

        return response()->json(['msg' => 'Invalid role. More types coming soon.'], 400);
    }

}
