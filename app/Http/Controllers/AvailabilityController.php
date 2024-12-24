<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Availability;

class AvailabilityController extends Controller
{
    // Method to display the upload form
    public function upload()
    {
        $availabilities = Availability::all()->keyBy('day');
        return view('admin.availability', compact('availabilities')); // Replace with your actual view path
    }

    // Method to handle form submission
    public function submitAvailability(Request $request)
    {   
        // Validate the incoming request data
        $request->validate([
            'availability' => 'array',
        ]);

        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
            $status = isset($request->input('availability')[$day]['enabled']) ? 1 : 0;

            Availability::updateOrCreate(
                ['day' => $day],
                [
                    'status' => $status,
                ]
            );
        }

        return redirect()->back()->with('success', 'Availability saved successfully!');
    }
}
