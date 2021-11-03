<?php

namespace JawabApp\Gamify\Exceptions;

class InvalidPayeeModel extends \Exception
{
    protected $message = 'payee() method must return a model which will get the points.';
}
