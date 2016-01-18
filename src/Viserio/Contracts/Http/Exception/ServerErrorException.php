<?php
namespace Viserio\Contracts\Http\Exception;

abstract class ServerErrorException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Server Error 5xx';

    /**
     * @var int
     */
    protected $code = 5;
}
