<?php
namespace Viserio\Contracts\Http\Exception;

class HttpVersionNotSupportedException extends ServerErrorException
{
    /**
     * @var string
     */
    protected $message = '505 HTTP Version Not Supported';

    /**
     * @var int
     */
    protected $code = 505;
}
