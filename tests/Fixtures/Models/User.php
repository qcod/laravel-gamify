<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use QCod\Gamify\Gamify;

/**
 * @property  int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \QCod\Gamify\Badge> $badges
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \QCod\Gamify\Reputation> $reputations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \QCod\Gamify\Tests\Fixtures\Models\Post> $posts
 */
class User extends Model
{
    use Gamify;

    public $table = 'test_users';

    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
