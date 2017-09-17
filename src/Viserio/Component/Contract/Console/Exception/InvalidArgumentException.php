<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Console\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements Exception
{
}
