<?php
namespace Viserio\Contracts\Http\Exception;

class NotImplementedException extends ServerErrorException
{
    /**
     * @var string
     */
    protected $message = '501 Not Implemented';

    /**
     * @var int
     */
    protected $code = 501;
}
