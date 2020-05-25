<?php


namespace App\Security;

use RuntimeException;
use Throwable;

class FileNotExistsException extends RuntimeException
{
    private $msg = "File does not exist.";

    public function __construct($msg, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}