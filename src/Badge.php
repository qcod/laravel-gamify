<?php

namespace JawabApp\Gamify;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        $table_name = app(config('gamify.payee_model'))->getTable() ?? 'users';
        $linkable_column_name = snake_case(str_singular($table_name));

        return $this->belongsToMany(
            config('gamify.payee_model'),
            $linkable_column_name . '_badges',
            'badge_id',
            $linkable_column_name . '_id'
        )
            ->withTimestamps();
    }

    /**
     * Award badge to a user
     *
     * @param $user
     */
    public function awardTo($user)
    {
        $this->users()->attach($user);
    }

    /**
     * Remove badge from user
     *
     * @param $user
     */
    public function removeFrom($user)
    {
        $this->users()->detach($user);
    }
}
