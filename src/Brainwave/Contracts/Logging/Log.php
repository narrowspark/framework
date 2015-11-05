<?php
namespace Brainwave\Contracts\Logging;

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

/**
 * Log.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
interface Log
{
    /**
     * Register a file log handler.
     *
     * @param string      $path
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useFiles($path, $level = 'debug', $processor = null, $formatter = null);

    /**
     * Register a daily file log handler.
     *
     * @param string      $path
     * @param int         $days
     * @param string      $level
     * @param object|null $processor
     * @param object|null $formatter
     */
    public function useDailyFiles($path, $days = 0, $level = 'debug', $processor = null, $formatter = null);
}
