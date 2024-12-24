<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VideoEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiVideoEventController extends Controller
{
    public function getVideoEvent(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user. Please check your token and try again.',
                ], 401);
            }

            $videoEvents = VideoEvent::where('user_id', $user->id)->with('liveStream')->get();

            if ($videoEvents->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No video events found for the user.',
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Video events retrieved successfully',
                'response' => $videoEvents,
            ]);

        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please provide a valid token.',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve video events',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLatestVideoEvent(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized user. Please check your token and try again.',
                ], 401);
            }

            // Get current time in Auckland timezone
            $nowInAuckland = Carbon::now(new \DateTimeZone('Pacific/Auckland'));

            // Log current time in Auckland
            \Log::info('Current Time in Auckland: ' . $nowInAuckland);

            // Set max limit to prevent infinite looping
            $maxDaysAhead = 7;
            $daysChecked = 0;

            $latestVideoEvent = null;  // Initialize variable

            // Check for the latest video event, iterating up to the max days ahead
            do {
                // Convert current day to text format ('Monday', 'Tuesday', etc.)
                $currentDayText = $nowInAuckland->format('l');

                // Check if the current day is enabled (available)
                $currentDayAvailability = DB::table('availabilities')
                    ->where('day', $currentDayText)
                    ->where('status', 1) // Check for enabled days only
                    ->exists();

                if ($currentDayAvailability) {
                    // Fetch the latest video event for the current or future day
                    $latestVideoEventQuery = VideoEvent::where(function ($query) use ($nowInAuckland) {
                        $query->whereDate('start_available_date', '<=', $nowInAuckland->toDateString())
                            ->whereDate('end_available_date', '>=', $nowInAuckland->toDateString());                          
                    })
                        ->orderBy('start_available_date', 'asc')
                        ->orderBy('start_event_time', 'asc')
                        ->with('liveStream')
                        ->get();  // Retrieve a collection of events for further filtering

                    // Filter out events whose 'end_available_date' falls on a disabled day
                    $filteredEvents = $latestVideoEventQuery->filter(function ($event) {
                        $endDayOfWeek = Carbon::parse($event->end_available_date)->format('l');

                        // Check if the end day is enabled
                        $isEndDayEnabled = DB::table('availabilities')
                            ->where('day', $endDayOfWeek)
                            ->where('status', 1)
                            ->exists();

                        // Only keep events where the end day is enabled
                        return $isEndDayEnabled;
                    })->first(); // Get the first matching event

                    // If an event is found after filtering, break the loop
                    if ($filteredEvents) {
                        $latestVideoEvent = $filteredEvents;  // Assign the filtered event
                        break;
                    }
                }

                // Move to the next day if the current day is disabled or no event found
                $nowInAuckland->addDay();
                $daysChecked++;
            } while ($daysChecked <= $maxDaysAhead); // Loop until maxDaysAhead is reached

            // If no events are found after the loop, return an appropriate message
            if (!$latestVideoEvent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No video events found for today or any future dates.',
                ], 200);
            }

            // Prepare event data for frontend display
            $eventData = [
                'id' => $latestVideoEvent->id,
                'available_start_time' => Carbon::parse($latestVideoEvent->start_event_time)->format('H:i'),
                'message' => $latestVideoEvent->message, // Start of available time
                'available_end_time' => Carbon::parse($latestVideoEvent->end_event_time)->format('H:i'),     // End of available time
                'live_stream' => $latestVideoEvent->liveStream,
                'next_available_day' => $nowInAuckland->toDateString(),  // Include next available day in response
                'other_event_data' => $latestVideoEvent->other_data // Add any other necessary event fields here
            ];

            // Return the latest video event with formatted dates and times
            return response()->json([
                'success' => true,
                'message' => 'Latest video event retrieved successfully',
                'response' => $eventData,
            ]);
        } catch (\Exception $e) {
            // Catch and log any exceptions
            \Log::error('Error fetching latest video event: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest video event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}