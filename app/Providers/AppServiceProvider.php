<?php

namespace App\Providers;

use App\Console\Commands\SendEventReminders;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
  /**
   * Register any application services.
   */
  public function register(): void {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void {
    $this->commands([
      SendEventReminders::class
    ]);
    Gate::define('update-event', function ($user, Event $event) {
      return $user->id === $event->user_id;
    });
  }
}
