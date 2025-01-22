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
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:255',
                'semester' => 'string|max:10',
                'department' => 'required|string|max:255',
                'gpa' => 'numeric|between:0,4.00',
                'national_id' => 'string|unique:users,national_id'
            ]);

            $token = str_replace('Bearer ', '', $request->header('Authorization'));

            $user = User::where('token', $token)->first();

            if (!$user) {
                $data = [
                    'msg' => 'Invalid token, User not found'
                ];
                return response()->json($data, 401);
            }

            $imgUrl = $user->image ? url('images/' . $user->image) : null;

            if ($request->hasFile('image')) {
                $img = $request->file('image');
                $imgName = rand() . time() . "." . $img->extension();
                $destinationPath = public_path('images');
                $img->move($destinationPath, $imgName);

                // Update user image and URL
                $user->update(['image' => $imgName]);
                $imgUrl = url('images/' . $imgName);
            }

            $user->update([
                'name' => $request->name,
                'semester' => $request->semester,
                'department' => $request->department,
                'gpa' => $request->gpa,
                'national_id' => $request->national_id
            ]);

            $data = [
                'msg' => 'Data completed successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'semester' => $user->semester,
                    'department' => $user->department,
                    'gpa' => $user->gpa,
                    'national_id' => $user->national_id,
                    'image' => $imgUrl
                ]
            ];
            return response()->json($data, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $data = [
                'msg' => $e->errors()
            ];
            return response()->json($data, 422);
        }
    }

}
