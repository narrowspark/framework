<?php

namespace Brainwave\Routing\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.8-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Routing\RouteParser;

/**
 * RouteCollectionTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
class RouteParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Asserts that the @ sign is correctly added when missing.
     */
    public function testNamedRouteAreHandledTheSameAsNotNamedRoute()
    {
        $parser = new RouteParser();

        $this->assertEquals($parser->parse('@bundle.named_route/my-route'), $parser->parse('/my-route'));
    }
}
