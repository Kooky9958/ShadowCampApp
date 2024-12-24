<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfileQuestion;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiTransationController extends Controller
{
    public function getTransation(Request $request)
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
    
            // Fetch the account ID associated with the authenticated user
            $accountId = $user->account->id; // Assuming you have an 'account' relationship set up
    
            // Fetch the user's transactions using the account ID
            $transactions = Transaction::where('account_id', $accountId)->get();
    
            // Get the count of transactions
            $transactionCount = $transactions->count();
    
            // Return the transactions with the token
            return response()->json([
                'success' => true,
                'message' => 'Payment Data Retrieved Successfully',
                'count' => $transactionCount,
                'response' => $transactions,
                // 'authorization' => [
                //     'token' => $request->bearerToken(), // Return the current token
                //     'type' => 'bearer',
                // ]
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
                'message' => 'Failed to retrieve payment data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getPer(Request $request)
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

            $totalPercentage = 100;
            $filledPercentage = 10;

            // Profile data fields check
            if (!empty($user->profile_photo_url)) $filledPercentage += 20; 
            if (!empty($user->name)) $filledPercentage += 5; 
            if (!empty($user->email)) $filledPercentage += 5; 
            if (!empty($user->gender)) $filledPercentage += 5; 
            if (!empty($user->age)) $filledPercentage += 5;
            if (!empty($user->height)) $filledPercentage += 5;
            if (!empty($user->weight)) $filledPercentage += 5;
            if (!empty($user->country)) $filledPercentage += 5;
            if (!empty($user->region)) $filledPercentage += 5;
            if (!empty($user->city)) $filledPercentage += 5;
            if (!empty($user->postcode)) $filledPercentage += 5;
            if (!empty($user->address_line1)) $filledPercentage += 5;
            if (!empty($user->hobbies)) $filledPercentage += 5;

            // Check if the user has answered any questions in the profile_questions table
            $profileQuestions = ProfileQuestion::where('user_id', $user->id)->first();

            // Add 10% if any specified fields are not empty
            if ($profileQuestions) {
                // List of fields to check
                $fieldsToCheck = [
                    'goals',
                    'mental_health_issues',
                    'hair_loss',
                    'birth_control',
                    'reproductive_disorder',
                    'weight_change',
                    'coffee_consumption',
                    'alcohol_consumption',
                    'other_goal',
                ];

                // Check if at least one field is filled
                foreach ($fieldsToCheck as $field) {
                    if (!empty($profileQuestions->$field)) {
                        $filledPercentage += 10;
                        break; // Exit loop after first filled field is found
                    }
                }
            }

            // Ensure the percentage does not exceed 100%
            $percentage = min($filledPercentage, $totalPercentage);

            $nameParts = explode(' ', $user->name);
    
            // Get the first letter of the first name and the first letter of the last name
            $firstNameInitial = substr($nameParts[0], 0, 1); // First name initial
            $lastNameInitial = isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''; // Last name initial if available
            // $firstNameInitial = !empty($user->name) ? substr($user->name, 0, 1) : 'U'; // Default to 'U' if name is empty
            // $lastNameInitial = !empty($user->name) && strlen($user->name) > 1 ? substr($user->name, 1, 1) : '';

            // Create a new profile URL using UI Avatars
            $user->newProfile = 'https://ui-avatars.com/api/?name=' . $firstNameInitial . $lastNameInitial . '&color=7F9CF5&background=EBF4FF';
           
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'percentage' => $percentage,
                'user' => $user,
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
                'message' => 'Failed to retrieve profile data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

}