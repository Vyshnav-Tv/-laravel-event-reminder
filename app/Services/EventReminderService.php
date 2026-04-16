<?php

namespace App\Services;

use App\Http\Requests\EventRequest;
use App\Models\event;
use App\Models\reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventReminderService
{

    public function create_events(EventRequest $request)
    {
        
        $event = event::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => Auth::id(),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event
        ]);
    }

    public function create_reminder($request)
    {
        request()->validate([
            'event_id' => 'required|exists:events,id',
            'reminder_time' => 'required|date',
            'status' => 'required'
        ]);
        $reminder = reminder::create([
            'event_id' => $request->event_id,
            'reminder_time' => $request->reminder_time,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully',
            'data' => $reminder
        ]);
    }

    public function getUser_events()
    {
        $events = event::where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    public function getUser_Reminders($event_id)
    {
      return  $reminders = reminder::where('event_id', $event_id)->get();

        return response()->json([
            'success' => true,
            'data' => $reminders
        ]);
    }

    public function update_event(EventRequest $request, $id)
    {
        $event = event::where('id', $id)
            ->first();

        $event->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }



    
}
?>