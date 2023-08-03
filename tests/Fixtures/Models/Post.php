<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use QCod\Gamify\Reputation;

/**
 * @property  int $id
 * @property-read \Illuminate\Database\Eloquent\Collection|\QCod\Gamify\Tests\Fixtures\Models\Reply[] $replies
 * @property-read \QCod\Gamify\Tests\Fixtures\Models\User $user
 * @property-read \QCod\Gamify\Tests\Fixtures\Models\Reply|null $bestReply
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \QCod\Gamify\Reputation> $reputations
 */
class Post extends Model
{
    public $table = 'test_posts';

    protected $guarded = [];

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class)->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bestReply(): HasOne
    {
        return $this->hasOne(Reply::class, 'id', 'best_reply_id');
    }

    public function reputations(): MorphMany
    {
        return $this->morphMany(Reputation::class, 'subject');
    }
}
