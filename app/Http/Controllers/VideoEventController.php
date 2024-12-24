<?php

namespace App\Http\Controllers;

use App\Models\VideoLiveStream;
use App\Models\VideoEvent;
use Illuminate\Http\Request;

class VideoEventController extends Controller
{
    public function upload(Request $request)
    {
        // Fetch live streams from the database
        $liveStreams = VideoLiveStream::all();
    
        if ($request->isMethod('post')) {
            // Validate the incoming request
            $request->validate([
                'live_stream_id' => 'required|exists:video_live_streams,id',
                'start_available_date' => 'nullable|date',
                'end_available_date' => 'nullable|date',
                'start_event_time' => 'required',
                'end_event_time' => 'required',
            ]);

            // Combine available date with event times
            $eventTimes = $this->combineEventTimes($request);

            // Check for overlapping events
            if ($this->hasOverlappingEvents($eventTimes['start_available_date'], $eventTimes['start'], $eventTimes['end'])) {
                return back()->withErrors(['message' => 'You cannot schedule overlapping events for the same day.']);
            }

            // Create the VideoEvent with validated data
            $this->createVideoEvent($request, $eventTimes);

            // Redirect to feedback page with success message
            return view('feedback', [
                'title' => 'Live Video Upload Success',
                'message' => 'Live Video event was created successfully.',
            ]);
        }

        // If it's a GET request, just show the form with live streams
        return view('admin.video_event_upload', compact('liveStreams'));
    }

    private function combineEventTimes(Request $request)
    {
        // Use the provided start and end available dates
        $startAvailableDate = $request->input('start_available_date') ?? now()->toDateString();
        $endAvailableDate = $request->input('end_available_date') ?? now()->toDateString();

        // Combine start and end available dates with event times
        $startEventTime = $startAvailableDate . ' ' . $request->input('start_event_time');
        $endEventTime = $endAvailableDate . ' ' . $request->input('end_event_time');

        return [
            'start_available_date' => $startAvailableDate,
            'end_available_date' => $endAvailableDate,
            'start' => $startEventTime,
            'end' => $endEventTime,
        ];
    }

    private function hasOverlappingEvents($startAvailableDate, $startEventTime, $endEventTime)
    {
        return VideoEvent::where('user_id', auth()->id())
            ->whereDate('start_available_date', $startAvailableDate)
            ->where(function ($query) use ($startEventTime, $endEventTime) {
                $query->whereBetween('start_event_time', [$startEventTime, $endEventTime])
                    ->orWhereBetween('end_event_time', [$startEventTime, $endEventTime])
                    ->orWhere(function ($query) use ($startEventTime, $endEventTime) {
                        $query->where('start_event_time', '<=', $startEventTime)
                                ->where('end_event_time', '>=', $endEventTime);
                    });
            })
            ->exists();
    }

    private function createVideoEvent(Request $request, array $eventTimes)
    {
        VideoEvent::create([
            'user_id' => auth()->id(),
            'live_video_id' => $request->input('live_stream_id'),
            'message' => $request->input('message'),
            'start_available_date' => $eventTimes['start_available_date'],
            'end_available_date' => $eventTimes['end_available_date'],
            'start_event_time' => $eventTimes['start'],
            'end_event_time' => $eventTimes['end'],
        ]);
    }


    public function edit($id)
    {
        $event = VideoEvent::find($id);
        $liveStreams = VideoLiveStream::all();

        return view('admin.video_event_edit', compact('event', 'liveStreams'));
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'live_stream_id' => 'required|exists:video_live_streams,id',
            'start_available_date' => 'required|date',
            'end_available_date' => 'required|date',
            'start_event_time' => 'required',
            'end_event_time' => 'required',
        ]);
        
        $event = VideoEvent::findOrFail($id);
        $event->update([
            'live_video_id' => $request->live_stream_id,
            'message' => $request->message,
            'start_available_date' => $request->start_available_date,
            'end_available_date' => $request->end_available_date, 
            'start_event_time' => $request->start_event_time,
            'end_event_time' => $request->end_event_time,
        ]);

        return view('feedback', ['title' => 'VideoEvent Update Success', 'message' => 'VideoEvent was updated successfully.']);
    }

    /**
     * Delete a specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $model_class_name The name of the model class
     * @param int $id The ID of the model to delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $model_class_name, $id)
    {
        $video = VideoEvent::find($id);
        $video->delete();

        // Redirect or return a response
        return redirect()->back()->with('status', 'Live Event deleted successfully!');
    }
}
