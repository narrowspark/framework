<?php
namespace Viserio\Contracts\Http\Exception;

abstract class AbstractClientErrorException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Client Error 4xx';

    /**
     * @var int
     */
    protected $code = 4;
}
