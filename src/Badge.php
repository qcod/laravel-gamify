<?php

namespace QCod\Gamify;

use Illuminate\Database\Eloquent\Model;
use QCod\Gamify\Events\BadgeGivenEvent;
use QCod\Gamify\Events\BadgeRemovedEvent;

class Badge extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(config('gamify.payee_model'), 'user_badges')
            ->withTimestamps();
    }

    /**
     * Award badge to a user
     *
     * @param $user
     */
    public function awardTo($user)
    {
        $event = config('gamify.events.badgeGiven', 'QCod\Gamify\Events\BadgeGivenEvent');
        event(new $event($this, $user));
        $this->users()->syncWithoutDetaching($user);
    }

    /**
     * Remove badge from user
     *
     * @param $user
     */
    public function removeFrom($user)
    {
        $event = config('gamify.events.badgeRemoved', 'QCod\Gamify\Events\BadgeGivenEvent');
        event(new $event($this, $user));
        $this->users()->detach($user);
    }
}
