<?php

namespace Brainwave\Support;

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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Support\Manager as ManagerContract;
use Interop\Container\ContainerInterface as ContainerInteropInterface;

/**
 * Manager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
abstract class Manager implements ManagerContract
{
    /**
     * The container instance.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInteropInterface$value='')
    {
        # code...
    }

    public function getContainer()
    {
        # code...
    }
}
