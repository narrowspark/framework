<?php
namespace Brainwave\Routing\Test\UrlGenerator;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Routing\UrlGenerator\GroupCountBasedDataGenerator;

/**
 * GroupCountBasedDataGeneratorTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class GroupCountBasedDataGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic functionality.
     */
    public function testFunctionality()
    {
        $collector = $this->getMockForAbstractClass('Brainwave\Contracts\Routing\RouteCollector');
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
        $collector = $this->getMockForAbstractClass('Brainwave\Contracts\Routing\RouteCollector');
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
