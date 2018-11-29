<?php

namespace QCod\Gamify\Tests\Models;

use QCod\Gamify\Gamify;
use Illuminate\Database\Eloquent\Model;

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
