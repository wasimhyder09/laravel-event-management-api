<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventReminders extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:send-event-reminders';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send event reminders';

  /**
   * Execute the console command.
   */
  public function handle() {
    $events = Event::with('attendees.user')->whereBetween('start_time', [now(), now()->addDay()])->get();
    $eventCount = $events->count();
    $eventLabel = Str::plural('event', $eventCount);
    $this->info("Found {$eventCount} {$eventLabel} events.");

    $events->each(
      fn ($event) => $event->attendees->each(
        fn ($attendee) => $this->info("Notifying the user {$attendee->user->id} with email: {$attendee->user->email} for event {$event->name}")
      )
    );

    $events->each(
      fn ($event) => $event->attendees->each(
        fn ($attendee) => $attendee->user->notify(
          new EventReminderNotification(
            $event
          )
        )
      )
    );

    $this->info('Notification sent successfully');
  }
}
