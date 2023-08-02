<?php

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use QCod\Gamify\Gamify;

class User extends Model
{
    use Gamify;

    protected $guarded = [];
    protected $connection = 'testbench';
    public $table = 'users';

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
