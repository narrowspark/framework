<?php
namespace Viserio\Cache\Exception;

use Viserio\Contracts\Cache\InvalidArgumentException as ExceptionContract;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionContract
{
}
