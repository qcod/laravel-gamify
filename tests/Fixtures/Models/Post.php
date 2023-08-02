<?php

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $table = 'posts';

    protected $guarded = [];
    protected $connection = 'testbench';

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
