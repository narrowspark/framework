<?php

declare(strict_types=1);
namespace Viserio\Container\Exception;

use Interop\Container\Exception\ContainerException as InteropNotFoundException;

/**
 * NotFoundException.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class NotFoundException extends \Exception implements InteropNotFoundException
{
}
