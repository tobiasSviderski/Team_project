<?php


namespace App\Security;

use RuntimeException;
use Throwable;

class FileNotMoveableException extends RuntimeException
{
    private $msg = "File coudn't be moved";
    public function __construct($msg, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}