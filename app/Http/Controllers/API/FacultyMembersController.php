<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FacultyMembers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FacultyMembersController extends Controller
{
    public function AllMembers()
    {
        $members = FacultyMembers::all();

        if ($members->isEmpty()) {
            return response()->json([
                'msg' => 'No faculty members found',
                'data' => [],
            ], 200);
        }
        
        return response()->json([
            'msg' => 'Faculty members retrieved successfully',
            'data' => $members,
        ], 200);
    }
    public function store(Request $request)
    {
        try{
        // Validate the request data
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'department' => 'required|string|in:CS,IS,IT,DS'
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

            $member = FacultyMembers::create([
                'name_ar' => $validated['name_ar'],
                'name_en' => $validated['name_en'],
                'department' => $validated['department'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'msg' => 'Faculty member added successfully',
                'data' => $member
            ], 200);
    }
    public function update(Request $request)
    {
        try{
        // Validate the request data
        $validated = $request->validate([
            'id' => 'required|exists:faculty_members,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'department' => 'required|string|in:CS,IS,IT,DS'
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $member = FacultyMembers::find($validated['id']);

        $member->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'department' => $validated['department'],
            'updated_at' => now()
        ]);

        return response()->json([
            'msg' => 'Faculty member updated successfully',
            'data' => $member
        ], 200);
    }
    public function delete(Request $request)
    {
        try{
        // Validate the request data
        $validated = $request->validate([
            'id' => 'required|exists:faculty_members,id',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $member = FacultyMembers::find($validated['id']);

        $member->delete();

        return response()->json([
            'msg' => 'Faculty member deleted successfully',
        ], 200);
    }
    
}
