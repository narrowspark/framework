<?php

namespace Brainwave\Routing\Test;

/*
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

use Brainwave\Container\Container;
use Brainwave\Routing\RouteCollection;
use Brainwave\Routing\RouteParser;
use FastRoute\DataGenerator\GroupCountBased;

/**
 * DispatcherTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    private function getRouteCollection()
    {
        return new RouteCollection(
            new Container(),
            new RouteParser(),
            new GroupCountBased()
        );
    }
}
