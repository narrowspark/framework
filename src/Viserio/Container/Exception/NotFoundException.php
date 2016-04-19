<?php
namespace Viserio\Container\Exception;

use Interop\Container\Exception\ContainerException as InteropNotFoundException;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements InteropNotFoundException
{
}
