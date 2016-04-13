<?php
namespace Viserio\Contracts\Http\Exception;

class RequestedRangeNotSatisfiableException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '416 Requested Range Not Satisfiable';

    /**
     * @var int
     */
    protected $code = 416;
}
