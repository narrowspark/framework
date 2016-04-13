<?php
namespace Viserio\Contracts\Http\Exception;

class LengthRequiredException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '411 Length Required';

    /**
     * @var int
     */
    protected $code = 411;
}
