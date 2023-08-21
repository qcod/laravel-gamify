<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Fake\PointTypes;

use QCod\Gamify\PointType;

class FakePayeeFieldPoint extends PointType
{
    protected $points = 10;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /** @var string payee model relation on subject */
    protected $payee = 'user';
}
