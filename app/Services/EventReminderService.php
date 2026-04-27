<?php

namespace App\Services;

use App\Http\Requests\EventRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\ReminderResource;
use App\Models\event;
use App\Models\reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventReminderService
{
    //create events
    public function create_events(EventRequest $request)
    {

        $event = event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'status' => $request->status,
            'user_id' => Auth::id(),
        ]);
        return response()->json([
            'success' => true,
            'event_id' => $event->id,
            'message' => 'Event created successfully'
        ]);
    }

    //create reminder
    public function create_reminder($request)
    {
        request()->validate([
            'event_id' => 'required|exists:events,id',
            'reminder_time' => 'required|date',
            'status' => 'nullable'
        ]);

        $event = event::where('event_id', $request->event_id)->where('user_id', Auth::id())->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found or does not belong to the user'
            ]);
        }

        reminder::create([
            'event_id' => $request->event_id,
            'reminder_time' => $request->reminder_time,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully'
        ]);
    }

    //get user events
    public function getUser_events()
    {
        //$events = event::where('user_id', Auth::id())->paginate(10);

        $query = event::where('user_id', Auth::id());

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        $events = $query->paginate(10);

        return response()->json([
            'success' => true,
            'user_id' => Auth::id(),
            'data' =>  EventResource::collection($events)
        ]);
    }

    //get user reminders
    public function getUser_Reminders($event_id)
    {
        $reminders = reminder::where('event_id', $event_id)->get();

        return response()->json([
            'success' => true,
            'data' => ReminderResource::collection($reminders),
        ]);
    }

    //update event
    public function update_event(EventRequest $request, $id)
    {
        $event = event::where('id', $id)
            ->first();

        $event->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
        ]);
    }

    //delete event
    public function delete_event($id)
    {

        $event = DB::table('events')->where('id', $id)
            ->first();


        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ]);
        }

        $reminder_exists = DB::table('reminders')
            ->where('event_id', $id)
            ->exists();

        if ($reminder_exists) {
            return response()->json([
                'success' => false,
                'message' => 'Event cannot be deleted because reminders exist'
            ]);
        }

        DB::table('events')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    //update event status
    public function update_event_status($id)
    {
        $event = event::where('id', $id)->first();
        $current_status = $event->status;
        $new_status = request()->query('status');

        if ($current_status === 'done' || $current_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Completed events cannot be modified'
            ]);
        }

        if (!in_array($new_status, ['not done', 'done', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status'
            ]);
        }

        $event->update([
            'status' => $new_status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    //update reminder status
    public function update_reminder_status($id)
    {
        $reminder = reminder::where('event_id', $id)->first();
        $current_status = $reminder->status;
        $new_status = request()->query('status');

        if ($current_status === 'done' || $current_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Completed reminders cannot be modified'
            ]);
        }

        if (!in_array($new_status, ['not_done', 'done', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status'
            ]);
        }

        $reminder->update([
            'status' => $new_status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $reminder
        ]);
    }

    //get events with filters
    public function get_events()
    {

        $query = event::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($time = request('time')) {

            if ($time === 'upcoming') {
                $query->whereDate('event_date', '>', now());
            }

            if ($time === 'past') {
                $query->whereDate('event_date', '<', now());
            }

            if ($time === 'today') {
                $query->whereDate('event_date', now());
            }
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => EventResource::collection($data)
        ]);
    }



    //create events and reminders together
    public function create_event_reminder(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'status' => 'nullable|in:not done,done,cancelled',
            'reminders' => 'nullable|array',
            'reminders.*.reminder_time' => 'nullable|date',
            'reminders.*.status' => 'nullable',
        ]);

        $event = event::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'status' => $validated['status'] ?? 'not done',
            'user_id' => Auth::id()
        ]);

        if (isset($validated['reminders'])) {
            foreach ($validated['reminders'] as $reminder) {
                reminder::create([
                    'event_id' => $event->id,
                    'reminder_time' => $reminder['reminder_time'],
                    'status' => $reminder['status'] ?? 'not_done'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Event and reminders created successfully'
            ]);
        }
    }
}
