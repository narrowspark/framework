<?php
namespace Viserio\Routing\Test;

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
