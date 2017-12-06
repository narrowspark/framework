<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class InvalidArgumentException extends BaseInvalidArgumentException implements NotFoundExceptionInterface
{
}
