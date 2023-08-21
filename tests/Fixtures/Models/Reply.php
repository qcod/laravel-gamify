<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property  int $id
 * @property-read \QCod\Gamify\Tests\Fixtures\Models\User $user
 * @property-read \QCod\Gamify\Tests\Fixtures\Models\Post $post
 */
class Reply extends Model
{
    public $table = 'test_replies';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
