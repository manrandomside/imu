<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user (Student or Alumni)
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'user_type' => 'required|in:student,alumni',
                'verification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // For alumni
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Validate email based on user type
            if (!User::validateEmailForUserType($data['email'], $data['user_type'])) {
                return response()->json([
                    'success' => false,
                    'message' => $data['user_type'] === 'student' 
                        ? 'Students must use @unud.ac.id email address' 
                        : 'Invalid email format'
                ], 422);
            }

            // Handle verification document upload for alumni
            $verificationDocumentPath = null;
            if ($data['user_type'] === 'alumni' && $request->hasFile('verification_document')) {
                $verificationDocumentPath = $request->file('verification_document')
                    ->store('verification_documents', 'public');
            }

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'user_type' => $data['user_type'],
                'verification_status' => $data['user_type'] === 'student' ? 'verified' : 'pending',
                'verification_document' => $verificationDocumentPath,
            ]);

            // Create token
            $token = $user->createToken('IMU-APP')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user->toApiArray(),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            // Find user by email
            $user = User::where('email', $credentials['email'])->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            // Check if user is verified
            if (!$user->isVerified()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not verified yet. Please wait for admin approval.',
                    'verification_status' => $user->verification_status
                ], 403);
            }

            // Revoke existing tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('IMU-APP')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user->toApiArray(),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data' => [
                    'user' => $user->toApiArray(),
                    'interests' => $user->interests->map(function($interest) {
                        return [
                            'id' => $interest->id,
                            'name' => $interest->name,
                            'icon' => $interest->icon,
                            'category' => $interest->category,
                        ];
                    }),
                    'profile_completion' => $user->getProfileCompletionPercentage(),
                    'is_profile_complete' => $user->isProfileComplete(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'nickname' => 'nullable|string|max:255',
                'prodi' => 'nullable|string|max:255',
                'fakultas' => 'nullable|string|max:255',
                'gender' => 'nullable|in:male,female',
                'bio' => 'nullable|string|max:1000',
                'age' => 'nullable|integer|min:16|max:100',
                'qualification' => 'nullable|string|max:255',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'looking_for' => 'nullable|array',
                'interests' => 'nullable|array',
                'interests.*' => 'exists:interests,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $data['profile_picture'] = $request->file('profile_picture')
                    ->store('profile_pictures', 'public');
            }

            // Update user profile
            $user->update($data);

            // Update interests if provided
            if (isset($data['interests'])) {
                $user->interests()->sync($data['interests']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user->fresh()->toApiArray(),
                    'profile_completion' => $user->getProfileCompletionPercentage(),
                    'is_profile_complete' => $user->isProfileComplete(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request)
    {
        try {
            // Revoke all tokens
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required to delete account',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password'
                ], 401);
            }

            // Delete profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Delete verification document if exists
            if ($user->verification_document) {
                Storage::disk('public')->delete($user->verification_document);
            }

            // Delete all tokens
            $user->tokens()->delete();

            // Delete user account
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}