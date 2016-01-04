<?php
namespace Viserio\Contracts\Http\Exception;

class BadGatewayException extends ServerErrorException
{
    /**
     * @var string
     */
    protected $message = '502 Bad Gateway';

    /**
     * @var int
     */
    protected $code = 502;
}
