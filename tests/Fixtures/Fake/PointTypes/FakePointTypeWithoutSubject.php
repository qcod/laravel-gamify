<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Fake\PointTypes;

use QCod\Gamify\PointType;
use QCod\Gamify\Tests\Fixtures\Models\User;

class FakePointTypeWithoutSubject extends PointType
{
    protected $point = 12;

    public function payee()
    {
        return new User();
    }
}
