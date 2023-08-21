<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Fake\PointTypes;

use QCod\Gamify\PointType;

class FakePointWithoutPoint extends PointType
{
    protected $payee = 'user';

    public function __construct($subject)
    {
        $this->subject = $subject;
    }
}
