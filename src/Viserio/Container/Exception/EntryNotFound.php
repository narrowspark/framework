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

use Exception;
use Interop\Container\Exception\NotFoundException;

/**
 * EntryNotFound.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class EntryNotFound extends Exception implements NotFoundException
{
    public static function fromId($id)
    {
        return new self(sprintf('The container entry "%s" was not found', $id));
    }
}
