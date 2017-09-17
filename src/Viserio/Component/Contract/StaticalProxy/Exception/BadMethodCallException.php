<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\StaticalProxy\Exception;

use BadMethodCallException as BaseBadMethodCallException;

class BadMethodCallException extends BaseBadMethodCallException implements Exception
{
}
