<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $table = 'test_posts';

    protected $guarded = [];

    public function replies()
    {
        return $this->hasMany(Reply::class)->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bestReply()
    {
        return $this->hasOne(Reply::class, 'id', 'best_reply_id');
    }

    public function reputations()
    {
        return $this->morphMany('QCod\Gamify\Reputation', 'subject');
    }
}
