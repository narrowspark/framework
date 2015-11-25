<?php
namespace Viserio\Routing\Test;

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

use Viserio\Routing\RouteParser;

/**
 * RouteCollectionTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7
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
