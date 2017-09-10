<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Support\Exception;

use BadMethodCallException as BaseBadMethodCallException;

class BadMethodCallException extends BaseBadMethodCallException implements Exception
{
}
