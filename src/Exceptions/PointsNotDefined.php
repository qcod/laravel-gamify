<?php

namespace JawabApp\Gamify\Exceptions;

use Exception;

class PointsNotDefined extends Exception
{
    protected $message = 'You must define a $points field or a getPoints() method.';
}
