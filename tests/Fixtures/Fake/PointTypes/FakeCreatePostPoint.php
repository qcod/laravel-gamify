<?php

declare(strict_types=1);

namespace QCod\Gamify\Tests\Fixtures\Fake\PointTypes;

use QCod\Gamify\PointType;

class FakeCreatePostPoint extends PointType
{
    protected $points = 10;

    public $allowDuplicates = false;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function payee()
    {
        return $this->getSubject()->user;
    }
}
