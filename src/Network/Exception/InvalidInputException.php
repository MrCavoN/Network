<?php
namespace Network\Exception;


class InvalidInputException extends \Exception
{
    public function __construct($message, $code, Exception $previous)
    {
        parent::__construct($message, $code, $previous);
    }
}