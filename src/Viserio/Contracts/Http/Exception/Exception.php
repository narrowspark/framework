<?php
namespace Viserio\Contracts\Http\Exception;

abstract class Exception extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'HTTP Exception';
}
