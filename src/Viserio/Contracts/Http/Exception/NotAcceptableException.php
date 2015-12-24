<?php
namespace Viserio\Contracts\Http\Exception;

class NotAcceptableException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '406 Not Acceptable';

    /**
     * @var int
     */
    protected $code = 406;
}
