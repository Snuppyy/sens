<?php

namespace App\Listeners;

use App\Events\SessionEditorIntercepted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSessionEditorInterceptedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SessionEditorIntercepted  $event
     * @return void
     */
    public function handle(SessionEditorIntercepted $event)
    {
        //
    }
}
