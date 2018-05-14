<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\Tests\Command;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Routing\Command\RouteListCommand;
use Viserio\Component\Routing\Route;
use Viserio\Component\Support\Invoker;
use Viserio\Contract\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class RouteListCommandTest extends MockeryTestCase
{
    public function testCommandWithNoRoutes(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Your application doesn't have any routes.\n", $output);
    }

    public function testCommand(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([new Route('GET', '/test/{param1}/{param2}', null)]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+-------------------------+------+------------+--------+\n| method   | uri                     | name | controller | action |\n+----------+-------------------------+------+------------+--------+\n| GET|HEAD | /test/{param1}/{param2} | -    | Closure    | -      |\n+----------+-------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithMethodFilter(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([new Route('GET', '/test/{param1}/{param2}', null), new Route('PUT', '/test/{param1}/{param2}', null)]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute(['--method' => 'put']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+--------+-------------------------+------+------------+--------+\n| method | uri                     | name | controller | action |\n+--------+-------------------------+------+------------+--------+\n| PUT    | /test/{param1}/{param2} | -    | Closure    | -      |\n+--------+-------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithNameFilter(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/test/{param1}/{param2}', null))->setName('test'), new Route('PUT', '/test/{param1}/{param2}', null)]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute(['--name' => 'test']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+-------------------------+------+------------+--------+\n| method   | uri                     | name | controller | action |\n+----------+-------------------------+------+------------+--------+\n| GET|HEAD | /test/{param1}/{param2} | test | Closure    | -      |\n+----------+-------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithPathFilter(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/foo/{param1}/{param2}', null))->setName('test'), new Route('PUT', '/test2', null)]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute(['--path' => 'foo']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+------------------------+------+------------+--------+\n| method   | uri                    | name | controller | action |\n+----------+------------------------+------+------------+--------+\n| GET|HEAD | /foo/{param1}/{param2} | test | Closure    | -      |\n+----------+------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithReverseFilter(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/foo/{param1}/{param2}', null))->setName('test'), new Route('PUT', '/test2', null)]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute(['--reverse' => 'r']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+------------------------+------+------------+--------+\n| method   | uri                    | name | controller | action |\n+----------+------------------------+------+------------+--------+\n| PUT      | /test2                 | -    | Closure    | -      |\n| GET|HEAD | /foo/{param1}/{param2} | test | Closure    | -      |\n+----------+------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithSortFilter(): void
    {
        $collection = \Mockery::mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/foo/{param1}/{param2}', null))->setName('c'), (new Route('PUT', '/test2', null))->setName('b')]);
        $router = \Mockery::mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);
        $command->setInvoker(new Invoker());

        $tester = new CommandTester($command);
        $tester->execute(['--sort' => 'name']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+------------------------+------+------------+--------+\n| method   | uri                    | name | controller | action |\n+----------+------------------------+------+------------+--------+\n| PUT      | /test2                 | b    | Closure    | -      |\n| GET|HEAD | /foo/{param1}/{param2} | c    | Closure    | -      |\n+----------+------------------------+------+------------+--------+\n", $output);
    }
}
