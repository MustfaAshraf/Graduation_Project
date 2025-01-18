<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function storeUserData(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:255',
                'semester' => 'string|max:10',
                'department' => 'required|string|max:255',
                'gpa' => 'numeric|between:0,4.00',
                'university_code' => 'nullable|string|unique:users,university_code',
                'national_id' => 'nullable|string|unique:users,national_id'
            ]);
    
            $token = str_replace('Bearer ', '', $request->header('Authorization'));
    
            $user = User::where('token', $token)->first();
    
            if($request->hasFile('image')) {

                $img = $request->file('image'); 
                $imgName = rand() . time() . "." . $img->extension(); 
                $destinationPath = public_path('images'); 
                $img->move($destinationPath, $imgName);
                $user->update(['image' => $imgName]);
            }
            $user->update([
                'name' => $request->name,
                'semester' => $request->semester,
                'department' => $request->department,
                'gpa' => $request->gpa,
                'university_code' => $request->university_code
            ]);
    

            $data = [
                'msg' => 'Data completed successfully',
                'data' => $user
            ];
            return response()->json($data,200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            $data = [
                'msg' => 'Validation error',
                'errors' => $e->errors()
            ];
            return response()->json($data,401);
        }
    }
}
