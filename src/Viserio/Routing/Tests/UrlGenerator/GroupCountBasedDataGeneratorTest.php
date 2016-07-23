<?php

declare(strict_types=1);
namespace Viserio\Routing\Tests\UrlGenerator;

use Viserio\Routing\UrlGenerator\GroupCountBasedDataGenerator;

class GroupCountBasedDataGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic functionality.
     */
    public function testFunctionality()
    {
        $collector = $this->getMockForAbstractClass('Viserio\Contracts\Routing\RouteCollector');
        $collector->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                // Static routes
                [
                    '/' => [
                        'GET' => [
                            'name' => 'home',
                            'controller' => 'handler1',
                        ],
                    ],
                ],
                // Dynamic routes
                [
                    'GET' => [
                        [
                            'regex' => '~^(?|/user/([^/]+)/show)$~',
                            'routeMap' => [
                                2 => [
                                    [
                                        'name' => 'user_show',
                                        'handler' => 'handler2',
                                    ],
                                    [
                                        'id' => 'id',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]));

        $generator = new GroupCountBasedDataGenerator($collector);
        $data = $generator->getData();
        $this->assertEquals([
            'home' => '/',
            'user_show' => [
                'path' => '/user/{id}/show',
                'params' => [
                    'id' => 'id',
                ],
            ],
        ], $data);
    }

    /**
     * Test invalid data handling.
     */
    public function testInvalidData()
    {
        $collector = $this->getMockForAbstractClass('Viserio\Contracts\Routing\RouteCollector');
        $collector->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                [],
                [
                    'GET' => [
                        [
                            'regex' => '~^(?|/user/([^/]+)/show|/user/([^/]+)/edit)$~',
                            'routeMap' => [
                                // Invalid index
                                0 => [
                                    [
                                        'name' => 'user_show',
                                        'handler' => 'handler2',
                                    ],
                                    [
                                        'id' => 'id',
                                    ],
                                ],
                                // Valid, but No "name" attribute
                                3 => [
                                    [
                                        'handler' => 'handler2',
                                    ],
                                    [
                                        'id' => 'id',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]));

        $generator = new GroupCountBasedDataGenerator($collector);
        $data = $generator->getData();
        $this->assertEquals([], $data);
    }
}
