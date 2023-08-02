<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Fake\PointTypes;

use QCod\Gamify\PointType;

class FakeWelcomeUserWithFalseQualifier extends PointType
{
    protected $points = 10;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function qualifier()
    {
        return false;
    }

    public function payee()
    {
        return $this->getSubject()->user;
    }
}
