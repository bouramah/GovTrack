<?php

namespace App\Providers;

use App\Events\ProjetCreated;
use App\Events\ProjetStatusChanged;
use App\Events\ProjetExecutionLevelUpdated;
use App\Events\TacheCreated;
use App\Events\TacheStatusChanged;
use App\Events\TacheExecutionLevelUpdated;
use App\Listeners\SendProjetCreatedNotification;
use App\Listeners\SendProjetStatusChangedNotification;
use App\Listeners\SendProjetExecutionLevelUpdatedNotification;
use App\Listeners\SendTacheCreatedNotification;
use App\Listeners\SendTacheStatusChangedNotification;
use App\Listeners\SendTacheExecutionLevelUpdatedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Événements de projets
        ProjetCreated::class => [
            SendProjetCreatedNotification::class,
        ],

        ProjetStatusChanged::class => [
            SendProjetStatusChangedNotification::class,
        ],

        ProjetExecutionLevelUpdated::class => [
            SendProjetExecutionLevelUpdatedNotification::class,
        ],

        // Événements de tâches
        TacheCreated::class => [
            SendTacheCreatedNotification::class,
        ],

        TacheStatusChanged::class => [
            SendTacheStatusChangedNotification::class,
        ],

        TacheExecutionLevelUpdated::class => [
            SendTacheExecutionLevelUpdatedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
