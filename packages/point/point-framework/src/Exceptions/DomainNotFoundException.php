<?php

namespace Point\Framework\Exceptions;

use Exception;

class DomainNotFoundException extends Exception
{
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }
}
