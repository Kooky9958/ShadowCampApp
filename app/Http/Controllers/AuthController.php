<?php

namespace App\Http\Controllers;

use App\Models\Account;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;


// 11-09-2024      Coded by        Vartik Anand 
// added user verification check 
class AuthController extends Controller
{
    //
    public static function login_user(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Check if the user exists and email is not verified
        if ($user && !$user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your email address is not verified.',
            ], 403); // HTTP status 403 Forbidden
        }

        // Attempt to log in
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            // Retrieve the corresponding account using the user's email
            $account = Account::where('email', $user->email)->first();

            if ($account) {
                // Check if the account's ID, name, and audience match the user's data
                if ($account->email === $user->email) {
                    return response()->json([
                        'status' => 'success',
                        'user' => $user,
                        'token' => $token,
                        'audience' => $account->audience
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Account details do not match or audience not found.',
                    ], 403);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account not found.',
                ], 404);
            }
        }

        // If login fails
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials',
        ], 401); // HTTP status 401 Unauthorized
    }



    // 11-09-2024          Coded by Vartik Anand 

    // This function will return the logged user audience type public static function 

    public static function get_audience(Request $request)
{
    // Validate the email input
    $credentials = $request->validate([
        'email' => ['required', 'email'],
    ]);

    // Find the user by email
    $user = User::where('email', $credentials['email'])->first();

    // Check if the user exists
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 404); // HTTP status 404 Not Found
    }

    // Check if the user's email is verified
    if (!$user->hasVerifiedEmail()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Your email address is not verified.',
        ], 403); // HTTP status 403 Forbidden
    }

    // Retrieve the account associated with the user
    $account = Account::where('email', $user->email)->first();

    if ($account) {
        // Determine the audience based on active subscriptions
        $audience_determined = null;

        if ($account->hasActiveSubTo('camp_precall')) {
            $audience_determined = 'precall';
        } elseif ($account->hasActiveSubTo('camp_delta_migrate') || $account->hasActiveSubTo('camp_delta9')) {
            $audience_determined = 'delta';
        }

        // Return success response with the determined audience
        return response()->json([
            'status' => 'success',
            'audience' => $audience_determined,
        ], 200); // HTTP status 200 OK
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Account not found.',
        ], 404); // HTTP status 404 Not Found
    }
}


    


    // Commented by Vartik Aanand  09-09-2024
// only it inserting recored in user model
    // coded by Rajesh 
    // public static function register_user(Request $request)
    // {

    //     // Validate the request parameters
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8', // Confirmed requires password_confirmation field
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' => 'Validation Error',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     } 

    //     // Create the user
    //     $user = Account::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     // Generate a token for the user
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     // Return the user data and token
    //     return response()->json([
    //         'message' => 'User successfully registered',
    //         'user' => $user,
    //         'token' => $token,
    //     ], 201);
    // }





}