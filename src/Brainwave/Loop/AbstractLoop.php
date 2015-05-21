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
 * @version     0.10-dev
 */

use Brainwave\Contracts\Loop\Loop as LoopContract;
use Interop\Container\ContainerInterface as ContainerContract;

/**
 * AbstractLoop.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
abstract class AbstractLoop implements LoopContract
{
    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerContract $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container = $container;
    }
}
