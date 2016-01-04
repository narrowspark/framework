<?php
namespace Viserio\Contracts\Http\Exception;

class GoneException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '410 Gone';

    /**
     * @var int
     */
    protected $code = 410;
}
