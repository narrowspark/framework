<?php
namespace Viserio\Contracts\Http\Exception;

class ProxyAuthenticationRequiredException extends AbstractClientErrorException
{
    /**
     * @var string
     */
    protected $message = '407 Proxy Authentication Required';

    /**
     * @var int
     */
    protected $code = 407;
}
