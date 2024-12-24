<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiUserProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            // Get the authenticated user via the token from the Authorization header
            $user = Auth::user(); // This will use the token from the request header automatically
    
            if (!$user) {
                // Handle the case where no user is authenticated
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user. Please check your token and try again.',
                ], 401);
            }
    
            // Fetch the user's data using the authenticated user's ID
            $userData = User::find($user->id); // Find the user by their ID
    
            if (!$userData) {
                // Handle the case where user data could not be found
                return response()->json([
                    'status' => false,
                    'message' => 'User data not found.',
                ], 200);
            }
    
            // Return the user data with a success message
            return response()->json([
                'success' => true,
                'message' => 'User profile data retrieved successfully',
                'data' => $userData, // Provide user data in the response
                'allHobbies'=>User::getAllHobbies(),
            ]);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Handle authentication-specific exceptions
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please provide a valid token.',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            // Handle any other exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function update(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user(); // This will use the token from the request header automatically
            if (!$user) {
                // Handle the case where no user is authenticated
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user. Please check your token and try again.',
                ], 401);
            }
    
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'profile_photo_path' => 'nullable|mimes:jpg,jpeg,png|max:1024',
                'gender' => 'required|string|in:Male,Female,Other',
                'age' => 'required|integer|min:0',
                'height' => 'required|numeric|min:0|max:999.99',
                'weight' => 'required|numeric|min:0|max:999.99',
                'address_line1' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'region' => 'nullable|string|max:255',
                'country' => 'required|string|max:255',
                'postcode' => 'required',
                'hobbies' => 'nullable|array',
                'hobbies.*' => 'string|max:255',
            ]);
    
            // Handle profile profile_photo_path if provided
            if ($request->hasFile('profile_photo_path')) {
                $profile_photo_path = $request->file('profile_photo_path');
                
                // Store the profile_photo_path
                $path = $profile_photo_path->store('profile_photos', 'public');
                
                // Optionally, delete old profile_photo_path if needed
                // Storage::disk('public')->delete($user->profile_photo_path);
                
                // Update the user's profile_photo_path path in the database
                $user->profile_photo_path = $path; // Assuming 'profile_photo_path' is a column in your users table
                $user->save();
            }
    
            // Update the user's profile with the validated data
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'gender' => $validatedData['gender'],
                'age' => $validatedData['age'],
                'height' => $validatedData['height'],
                'weight' => $validatedData['weight'],
                'address_line1' => $validatedData['address_line1'],
                'city' => $validatedData['city'],
                'region' => $validatedData['region'],
                'country' => $validatedData['country'],
                'postcode' => $validatedData['postcode'],
                'hobbies' => $validatedData['hobbies'] ? json_encode($validatedData['hobbies']) : null,
            ]);
    
            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'User Profile Updated Successfully',
                'response' => $user,
                'authorization' => [
                    'token' => $request->bearerToken(), // Return the current token if needed
                    'type' => 'bearer',
                ]
            ]);
    
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to Update User Profile data',
                'error' => $e->getMessage(),
            ], 500); // Return a 500 status code for server error
        }
    }
    

    public function update_password(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user(); // This will use the token from the request header automatically
            if (!$user) {
                // Handle the case where no user is authenticated
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user. Please check your token and try again.',
                ], 401);
            }
    
            // Validate the incoming request data
            $validatedData = $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|confirmed|min:8', // Adding min length for security
            ]);
    
            // Check if the current password matches
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 400);
            }
    
            // Update the password
            $user->password = Hash::make($validatedData['password']);
            $user->save();
    
            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
    
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password',
                'error' => $e->getMessage(),
            ], 500); // Return a 500 status code for server error
        }
    }

    public function country()
    {
        try {
            $countries = Country::all();
            $count = $countries->count();

            return response()->json([
                'success' => true,
                'message' => 'Country data retrieved successfully',
                'count' => $count,
                'data' => $countries, 
            ], 200);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please provide a valid token.',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve country data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function region($country_id)
    {
        try {
            $regions = Region::where('country_id', $country_id)->get();
            $count = $regions->count();

            return response()->json([
                'success' => true,
                'message' => 'Region data retrieved successfully',
                'count' => $count,
                'data' => $regions, 
            ], 200);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please provide a valid token.',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Region data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}