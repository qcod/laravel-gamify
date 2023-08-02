<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Fake\PointTypes;

use QCod\Gamify\PointType;

class FakePointTypeWithoutPayee extends PointType
{
    protected $point = 24;

    public function payee()
    {
    }
}
