<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function storeUserData(Request $request)
    {
        try {
            $token = str_replace('Bearer ', '', $request->header('Authorization'));

            $user = User::where('token', $token)->first();

            if (!$user) {
                $data = [
                    'msg' => 'Invalid token, User not found'
                ];
                return response()->json($data, 401);
            }

            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:255',
                'semester' => 'string|max:10',
                'department' => 'required|string|max:255',
                'gpa' => 'numeric|between:0,4.00',
                'university_id' => [
                    'string',
                    Rule::unique('users', 'university_id')->ignore($user->id)
                ],
                'national_id' => [
                    'string',
                    Rule::unique('users', 'national_id')->ignore($user->id)
                ],
            ]);

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
                'university_id' => $request->university_id,
                'national_id' => $request->national_id,
            ]);

            $data = [
                'msg' => 'Data completed successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'semester' => $user->semester,
                    'department' => $user->department,
                    'gpa' => $user->gpa,
                    'university_id' => $user->university_id,
                    'national_id' => $user->national_id,
                    'image' => $imgUrl,
                ],
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
