<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller implements HasMiddleware {

  use CanLoadRelationships;

  public static function middleware(): array {
    return [
      new Middleware('auth:sanctum', except: ['index', 'show']),
    ];
  }
  private array $relations = ['user', 'attendees', 'attendees.user'];
  public function index() {

    $query = $this->loadRelationships(Event::query());

    return EventResource::collection(
      $query->latest()->paginate()
    );
  }

  public function store(Request $request) {
    $event = Event::create([
      ...$request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_time' => 'required|date',
        'end_time' => 'required|date|after_or_equal:start_time'
      ]),
      'user_id' => $request->user()->id
    ]);

    return new EventResource($this->loadRelationships($event));
  }

  public function show(Event $event) {
    return new EventResource($this->loadRelationships($event));
  }

  public function update(Request $request, Event $event) {
//    $this->authorize('update-event', $event);
    if(Gate::denies('update-event', $event)) {
      abort(403, 'Unauthorized action.');
    }
    $event->update(
      $request->validate([
        'name' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'start_time' => 'sometimes|date',
        'end_time' => 'sometimes|date|after_or_equal:start_time'
      ])
    );
    return new EventResource($this->loadRelationships($event));
  }

  public function destroy(Event $event) {
    $event->delete();
    return response()->json([
      'message' => 'Event deleted successfully!'
    ]);
  }
}
