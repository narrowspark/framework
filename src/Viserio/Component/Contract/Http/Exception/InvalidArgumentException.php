<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Http\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements Exception
{
}
