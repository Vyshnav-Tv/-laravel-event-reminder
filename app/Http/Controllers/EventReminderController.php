<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Services\EventReminderService;
use Illuminate\Http\Request;

class EventReminderController extends Controller
{
    protected $eventReminderService;

    public function __construct(EventReminderService $eventReminderService)
    {
        $this->eventReminderService = $eventReminderService;
    }

    public function create_events(EventRequest $request)
    {
        return $this->eventReminderService->create_events($request);
    }


    public function create_reminder(Request $request)
    {
        return $this->eventReminderService->create_reminder($request);
    }

    public function getUser_events()
    {
        return $this->eventReminderService->getUser_events();
    }

    public function getUser_Reminders($event_id)
    {
        return $this->eventReminderService->getUser_Reminders($event_id);
    }

    public function update_event(EventRequest $request, $id)
    {
        return $this->eventReminderService->update_event($request, $id);
    }

    public function delete_event($id){
        return $this->eventReminderService->delete_event($id);
    }

    public function update_event_status( $id){
        return $this->eventReminderService->update_event_status($id);
    }

    public function update_reminder_status($id)
    {
        return $this->eventReminderService->update_reminder_status($id);
    }

    public function get_events(){
        return $this->eventReminderService->get_events();
    }

}
