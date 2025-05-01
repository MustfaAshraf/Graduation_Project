<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    public function getAllUsers(Request $request)
    {
            // Fetch users and exclude some fields
            $users = UserResource::collection(User::all());

            if ($users->isEmpty()) {
                return response()->json([
                    'msg' => 'No users found.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'msg' => 'All users retrieved successfully.',
                'data' => $users,
            ], 200);
    }

    public function addUser(Request $request)
    {
        try{
        $request->validate([
            'role' => 'required|in:0,1',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $user = User::create([
            'role' => $request->role,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'msg' => 'User added successfully.',
            'data' => new UserResource($user),
        ], 200);
    }

    public function updateUser(Request $request)
    {
        try{
        $request->validate([
            'id' => 'required|exists:users,id',
            'role' => 'nullable|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,',
            'university_id' => 'nullable|unique:users,university_id,',
            'national_id' => 'nullable|unique:users,national_id,',
            'gpa' => 'nullable|numeric|between:0,4.00',
            'semester' => 'nullable|string|max:10',
            'department' => 'nullable|string|max:255',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $user = User::find($request->id);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $imgName = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('images');
            $img->move($destinationPath, $imgName);

            // Update user image
            $user->update(['image' => $imgName]);
        }

        $user->update($request->only([
            'role', 'name', 'email', 'semester', 'department',
            'gpa', 'university_id', 'national_id'
        ]));

        return response()->json([
            'msg' => 'User updated successfully.',
            'data' => new UserResource($user),
        ], 200);
    }

    public function deleteUser(Request $request)
    {
        // Validate the request
        try{
            $request->validate([
                'id' => 'required|exists:users,id',
            ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'msg' => $e->errors(),
                ], 422);
            }

        // Find and delete the user
        $user = User::find($request->id);

        $user->delete();

        return response()->json([
            'msg' => 'User deleted successfully.'
        ], 200);
    }
}
