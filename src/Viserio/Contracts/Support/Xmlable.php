<?php
namespace Viserio\Contracts\Support;

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
 * @version     0.10.0
 */

/**
 * Xmlable.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
 */
interface Xmlable
{
    /**
     * Convert the object to its XML representation.
     *
     * @return string
     */
    public function toXml();
}
