<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Viserio\Routing\RouteParser;

class RouteParserTest extends \PHPUnit_Framework_TestCase
{
    // public function testParse($uri, array $expectedSegments)
    // {
    //     $parser = new RouteParser();

    //     $this->assertEquals($expectedSegments, $parser->parse($uri));
    // }

     public function parsingExamples()
    {
        return [
            [
                // Empty route
                '',
                [],
                []
            ],
            [
                // Empty route
                '/',
                [],
                [new StaticSegment('')]
            ],
            [
                '/user',
                [],
                [new StaticSegment('user')]
            ],
        ];
    }
}
