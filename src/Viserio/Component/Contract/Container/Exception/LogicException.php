<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

use LogicException as BaseLogicException;
use Psr\Container\ContainerExceptionInterface;

class LogicException extends BaseLogicException implements ContainerExceptionInterface
{
}
