<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support\Exception;

use BadMethodCallException as BaseBadMethodCallException;

class BadMethodCallException extends BaseBadMethodCallException implements Exception
{
}
