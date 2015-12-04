<?php
namespace Viserio\Loop;

use Viserio\Contracts\Loop\Adapter as AdapterContract;

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
