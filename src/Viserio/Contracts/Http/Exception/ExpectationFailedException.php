<?php
namespace Viserio\Contracts\Http\Exception;

class ExpectationFailedException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '417 Expectation Failed';

    /**
     * @var int
     */
    protected $code = 417;
}
