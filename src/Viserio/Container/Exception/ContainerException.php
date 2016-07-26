<?php
declare(strict_types=1);
namespace Viserio\Container\Exception;

use Interop\Container\Exception\ContainerException as InteropContainerException;

/**
 * ContainerException.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class ContainerException extends \Exception implements InteropContainerException
{
}
