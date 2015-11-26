<?php
namespace Viserio\Container\Exception;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Interop\Container\Exception\NotFoundException;

/**
 * UnsupportedDefinition.
 * The definition is not supported by the container.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class UnsupportedDefinition extends \Exception implements NotFoundException
{
    public static function fromDefinition($definition)
    {
        return new self(sprintf('%s is not a supported definition', get_class($definition)));
    }
}
