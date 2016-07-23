<?php

declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\RouteParser;

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
