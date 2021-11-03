<?php

namespace JawabApp\Gamify\Listeners;

use JawabApp\Gamify\Events\ReputationChanged;
use Illuminate\Support\Facades\Log;

class SyncBadges
{
    /**
     * Handle the event.
     *
     * @param  ReputationChanged  $event
     * @return void
     */
    public function handle(ReputationChanged $event)
    {
        $event->user->syncBadges();
    }
}
