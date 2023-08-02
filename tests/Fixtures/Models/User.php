<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use QCod\Gamify\Gamify;

class User extends Model
{
    use Gamify;

    // used in config to alter a project level model
    public $table = 'test_users';

    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
