<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

use Exception as BaseException;
use Psr\Container\ContainerExceptionInterface;

class UnresolvableDependencyException extends BaseException implements ContainerExceptionInterface
{
}
