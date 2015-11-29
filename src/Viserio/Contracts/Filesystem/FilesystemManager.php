<?php
namespace Viserio\Contracts\Filesystem;

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
 * FilesystemManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface FilesystemManager
{
    /**
     * Get a filesystem implementation.
     *
     * @param string|null $name
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
