<?php

namespace QCod\Gamify\Tests\Fixtures\Badges;

use QCod\Gamify\BadgeType;
use QCod\Gamify\Tests\Fixtures\Models\User;

class FirstContribution extends BadgeType
{
    /**
     * Description for badge
     *
     * @var string
     */
    protected $description = 'Great! This is the begining of great things.';

    /**
     * Check is user qualifies for badge
     *
     * @param User $user
     * @return bool
     */
    public function qualifier($user)
    {
        return $user->posts()->count() == 1;
    }
}
