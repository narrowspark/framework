<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption\Exception;

use TypeError as BaseTypeError;

class InvalidTypeException extends BaseTypeError implements Exception
{
}
