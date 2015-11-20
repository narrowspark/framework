<?php
namespace Viserio\Loop;

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

use Viserio\Contracts\Loop\Adapter as AdapterContract;

/**
 * Repository.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class Repository
{
    /**
     * The loop adapter implementation.
     *
     * @var \Viserio\Contracts\Loop\Adapter
     */
    protected $adapter;

    /**
     * Loop driver supported.
     *
     * @var bool
     */
    protected static $supported = false;

    /**
     * Create a new loop repository instance.
     *
     * @param \Viserio\Contracts\Loop\Adapter
     */
    public function __construct(AdapterContract $adapter)
    {
        $this->adapter = $adapter;

        self::$supported = $driver::isSupported();
    }

    /**
     * Check if the cache driver is supported.
     *
     * @return bool
     */
    public static function isSupported()
    {
        return static::$supported;
    }
}
