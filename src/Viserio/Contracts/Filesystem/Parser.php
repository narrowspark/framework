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
 * Parser.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Parser
{
    /**
     * Loads a file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @return array|string|null
     */
    public function load($filename, $group = null);

    /**
     * Checking if file ist supported.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function supports($filename);

    /**
     * Format a data file for saving.
     *
     * @param array $data data
     *
     * @return string|false data export
     */
    public function format(array $data);
}
