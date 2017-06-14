<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Command;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\Command\RouteListCommand;
use Viserio\Component\Routing\Route;

class RouteListCommandTest extends MockeryTestCase
{
    public function testCommandWithNoRoutes()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Your application doesn't have any routes.\n", $output);
    }

    public function testCommand()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([new Route('GET', '/test/{param1}/{param2}', null)]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+-------------------------+------+------------+--------+\n| method   | uri                     | name | controller | action |\n+----------+-------------------------+------+------------+--------+\n| GET|HEAD | /test/{param1}/{param2} | -    | Closure    | -      |\n+----------+-------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithMethodFilter()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([new Route('GET', '/test/{param1}/{param2}', null), new Route('PUT', '/test/{param1}/{param2}', null)]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute(['--method' => 'put']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+--------+-------------------------+------+------------+--------+\n| method | uri                     | name | controller | action |\n+--------+-------------------------+------+------------+--------+\n| PUT    | /test/{param1}/{param2} | -    | Closure    | -      |\n+--------+-------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithNameFilter()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/test/{param1}/{param2}', null))->setName('test'), new Route('PUT', '/test/{param1}/{param2}', null)]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute(['--name' => 'test']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+-------------------------+------+------------+--------+\n| method   | uri                     | name | controller | action |\n+----------+-------------------------+------+------------+--------+\n| GET|HEAD | /test/{param1}/{param2} | test | Closure    | -      |\n+----------+-------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithPathFilter()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/foo/{param1}/{param2}', null))->setName('test'), new Route('PUT', '/test2', null)]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute(['--path' => 'foo']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+------------------------+------+------------+--------+\n| method   | uri                    | name | controller | action |\n+----------+------------------------+------+------------+--------+\n| GET|HEAD | /foo/{param1}/{param2} | test | Closure    | -      |\n+----------+------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithReverseFilter()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/foo/{param1}/{param2}', null))->setName('test'), new Route('PUT', '/test2', null)]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute(['--reverse' => 'r']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+------------------------+------+------------+--------+\n| method   | uri                    | name | controller | action |\n+----------+------------------------+------+------------+--------+\n| PUT      | /test2                 | -    | Closure    | -      |\n| GET|HEAD | /foo/{param1}/{param2} | test | Closure    | -      |\n+----------+------------------------+------+------------+--------+\n", $output);
    }

    public function testCommandWithSortFilter()
    {
        $collection = $this->mock(RouteCollectionContract::class);
        $collection->shouldReceive('getRoutes')
            ->once()
            ->andReturn([(new Route('GET', '/foo/{param1}/{param2}', null))->setName('c'), (new Route('PUT', '/test2', null))->setName('b')]);
        $router = $this->mock(RouterContract::class);
        $router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($collection);

        $command = new RouteListCommand($router);

        $tester = new CommandTester($command);
        $tester->execute(['--sort' => 'name']);

        $output = $tester->getDisplay(true);

        self::assertEquals("+----------+------------------------+------+------------+--------+\n| method   | uri                    | name | controller | action |\n+----------+------------------------+------+------------+--------+\n| PUT      | /test2                 | b    | Closure    | -      |\n| GET|HEAD | /foo/{param1}/{param2} | c    | Closure    | -      |\n+----------+------------------------+------+------------+--------+\n", $output);
    }
}
