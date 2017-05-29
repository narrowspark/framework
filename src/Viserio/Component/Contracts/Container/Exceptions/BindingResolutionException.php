<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class BindingResolutionException extends Exception implements ContainerExceptionInterface
{
}
