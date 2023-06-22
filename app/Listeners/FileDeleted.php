<?php

namespace App\Listeners;

use Storage;

use App\Events\FileDeleted as Event;

class FileDeleted
{
    /**
     * Handle the event.
     *
     * @param  FileDeleted  $event
     * @return void
     */
    public function handle(Event $event)
    {
        Storage::delete($event->file->file);
    }
}
