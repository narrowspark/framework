<?php
namespace Viserio\Contracts\Http\Exception;

class BadRequestException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '400 Bad Request';

    /**
     * @var int
     */
    protected $code = 400;
}
