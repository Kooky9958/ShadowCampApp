<?php
namespace App\Http\Controllers;

use App\Models\ProfileQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiProfileQuestionController extends Controller
{

    public function get_profile_question(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // If the user is not authenticated, return an error response
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized user. Please check your token and try again.',
            ], 401);
        }

        // Retrieve the profile question for the authenticated user
        $profileQuestion = ProfileQuestion::where('user_id', $user->id)->first();

        // If no profile question is found, return a not found response
        if (!$profileQuestion) {
            return response()->json([
                'status' => false,
                'message' => 'Profile question not found.',
            ], 200);
        }

        // Return the profile question record
        return response()->json([
            'success' => true,
            'message' => 'Profile Questions get Successfully',
            'data' => $profileQuestion,
        ], 200);
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user. Please check your token and try again.',
                ], 401);
            }

            $validatedData = $request->validate([
                'goals' => 'required|array',
            ]);

            $mentalHealthIssues = $request->has('mental_health_issues') && is_array($request->mental_health_issues) 
                                ? $request->mental_health_issues 
                                : null;

            ProfileQuestion::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'goals' => json_encode($validatedData['goals']),
                    'mental_health_issues' => $mentalHealthIssues ? json_encode($mentalHealthIssues) : null,
                    'hair_loss' => $request->input('hair_loss', null),
                    'birth_control' => $request->input('birth_control', null),
                    'reproductive_disorder' => $request->input('reproductive_disorder', null),
                    'weight_change' => $request->input('weight_change', null),
                    'coffee_consumption' => $request->input('coffee_consumption', null),
                    'alcohol_consumption' => $request->input('alcohol_consumption', null),
                    'other_goal' => $request->input('other_goal', null),
                ]
            );

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}