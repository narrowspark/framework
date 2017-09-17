<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class BindingResolutionException extends Exception implements ContainerExceptionInterface
{
}
