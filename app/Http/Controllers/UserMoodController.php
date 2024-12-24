<?php

namespace App\Http\Controllers;

use App\Models\NonNegotiable;
use App\Models\UserMood;
use App\Models\Mood;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserMoodController extends Controller
{

    /**
                 * Store or update the user's mood for a specific date.
                 *
                 * @param Request $request
                 * @return JsonResponse
                 */
    /**
     * Store or update the user's mood for a specific date.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'mood_id' => 'required|exists:moods,id',
            'date' => 'nullable|date|date_format:Y-m-d',
        ]);

        $user = Auth::user();
        $date = $validatedData['date'] ?? now()->format('Y-m-d');

        try {
            // Store or update the user's mood
            $userMood = UserMood::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'mood_date' => $date,
                ],
                [
                    'mood_id' => $validatedData['mood_id'],
                ]
            );

            // Check if a non-negotiable already exists for this user and date
            $existingNonNegotiable = NonNegotiable::where('user_id', $user->id)
                ->where('date', $date)
                ->where('type', 'mood')
                ->first();

            if (!$existingNonNegotiable) {
                // No existing non-negotiable, create a new one
                $nonNegotiable = NonNegotiable::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'type' => 'mood',
                    'completed' => true,
                ]);
            } else {
                // Update the existing non-negotiable if needed
                $existingNonNegotiable->update([
                    'completed' => true,
                ]);
            }

            $message = $userMood->wasRecentlyCreated
                ? 'Mood recorded successfully!'
                : 'Mood updated successfully!';

            Log::info('User mood updated successfully', [
                'user_id' => $user->id,
                'mood_id' => $validatedData['mood_id'],
                'mood_date' => $date,
                'non_negotiable_id' => $existingNonNegotiable->id ?? $nonNegotiable->id,
            ]);

            return response()->json([
                'message' => $message,
                'mood' => $userMood,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update user mood', [
                'user_id' => $user->id,
                'mood_id' => $validatedData['mood_id'],
                'mood_date' => $date,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update mood.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function index(Request $request): JsonResponse
    {
        try {
            // Fetch all moods from the database
            $moods = Mood::all();

            // Get the current date
            $currentDate = now()->format('Y-m-d');

            // Get the authenticated user
            $user = Auth::user();

            // Fetch the user's mood for today with eager loading to avoid multiple queries
            $userMood = UserMood::where('user_id', $user->id)
                ->where('mood_date', $currentDate)
                ->first();

            // Add a `selected` field to each mood based on whether the user has selected it for today
            $moods = $moods->map(function ($mood) use ($userMood) {
                // Check if the user's selected mood matches the current mood
                $mood->selected = $userMood && $userMood->mood_id == $mood->id;
                return $mood;
            });

            // Return the list of moods with the selected flag
            return response()->json([
                'message' => 'Moods retrieved successfully!',
                'moods' => $moods,
            ], 200);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching moods:', ['error' => $e->getMessage()]);

            // Return a failure response
            return response()->json([
                'message' => 'Failed to retrieve moods.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user moods between a specific date range.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserMoods(Request $request): JsonResponse
    {
        try {
            // Validate the date range parameters with custom messages
            $validated = $request->validate([
                'periodStart' => 'required|date|before_or_equal:periodEnd',
                'periodEnd'   => 'required|date|after_or_equal:periodStart',
            ], [
                'periodStart.required' => 'The start date is required.',
                'periodStart.date'     => 'The start date must be a valid date.',
                'periodEnd.required'   => 'The end date is required.',
                'periodEnd.date'       => 'The end date must be a valid date.',
                'periodEnd.after_or_equal' => 'The end date must be after or equal to the start date.'
            ]);

            // Fetch moods for the authenticated user within the given date range
            $userMoods = UserMood::where('user_id', auth()->id())
                ->whereBetween('mood_date', [$validated['periodStart'], $validated['periodEnd']])
                ->with('mood') // Eager load the 'mood' relationship, if relevant
                ->get();

            // Return the moods as a JSON response
            return response()->json($userMoods, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching user moods: ' . $e->getMessage(), [
                'userId' => auth()->id(),
                'periodStart' => $request->input('periodStart'),
                'periodEnd' => $request->input('periodEnd'),
            ]);

            // Handle general exceptions
            return response()->json([
                'error' => 'An error occurred while fetching user moods. Please try again later.'
            ], 500);
        }
    }
}
