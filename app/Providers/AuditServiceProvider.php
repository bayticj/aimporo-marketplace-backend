<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register audit event listeners
        \Event::listen(\OwenIt\Auditing\Events\Auditing::class, function ($event) {
            // You can add custom logic here before an audit is created
        });

        \Event::listen(\OwenIt\Auditing\Events\Audited::class, function ($event) {
            // You can add custom logic here after an audit is created
        });

        \Event::listen(\OwenIt\Auditing\Events\AuditFailed::class, function ($event) {
            // You can add custom logic here when an audit fails
            \Log::error('Audit failed for model: ' . get_class($event->model), [
                'model_id' => $event->model->getKey(),
                'event' => $event->event,
                'error' => $event->error,
            ]);
        });
    }
} 