<?php

namespace Brainwave\Loop;

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

use Brainwave\Contracts\Loop\Adapter as AdapterContract;


/**
 * Repository.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class Repository extends AnotherClass
{
    /**
     * The loop adapter implementation.
     *
     * @var \Brainwave\Contracts\Loop\Adapter
     */
    protected $adapter;

    /**
     * Create a new loop repository instance.
     *
     * @param \Brainwave\Contracts\Loop\Adapter
     */
    public function __construct(AdapterContract $adapter)
    {
        $this->adapter = $adapter;
    }
}
