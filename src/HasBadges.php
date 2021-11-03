<?php

namespace JawabApp\Gamify;

trait HasBadges
{
    /**
     * Badges user relation
     *
     * @return mixed
     */
    public function badges()
    {
        $table_name = app(config('gamify.payee_model'))->getTable() ?? 'users';
        $linkable_column_name = snake_case(str_singular($table_name));

        return $this->belongsToMany(
            config('gamify.payee_model'),
            $linkable_column_name . '_badges',
            $linkable_column_name . '_id',
            'badge_id'
        )
            ->withTimestamps();
    }

    /**
     * Sync badges for qiven user
     *
     * @param $user
     */
    public function syncBadges($user = null)
    {
        $user = is_null($user) ? $this : $user;

        $badgeIds = app('badges')->filter
            ->qualifier($user)
            ->map->getBadgeId();

        $user->badges()->sync($badgeIds);
    }
}
