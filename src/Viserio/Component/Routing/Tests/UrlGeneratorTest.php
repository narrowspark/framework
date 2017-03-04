<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\UrlGenerator;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\UriFactory;

class UrlGeneratorTest extends MockeryTestCase
{
    public function testAbsoluteUrlWithPort80()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('http://localhost/testing', $url);
    }

    public function testAbsoluteSecureUrlWithPort443()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, array('HTTPS' => 'on'))->generate('testing', array(), UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('https://localhost/testing', $url);
    }

    public function testAbsoluteUrlWithNonStandardPort()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, array('SERVER_PORT' => 8080))->generate('testing', array(), UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('http://localhost:8080/testing', $url);
    }

    public function testAbsoluteSecureUrlWithNonStandardPort()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, array('HTTPS' => 'on', 'SERVER_PORT' => 8080))->generate('testing', array(), UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('https://localhost:8080/testing', $url);
    }

    public function testRelativeUrlWithoutParameters()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', array(), UrlGeneratorContract::ABSOLUTE_PATH);

        $this->assertEquals('/testing', $url);
    }

    // public function testRelativeUrlWithParameter()
    // {
    //     $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

    //     $url = $this->getGenerator($routes)->generate('testing', array('param1' => 'bar'), UrlGeneratorContract::ABSOLUTE_PATH);

    //     $this->assertEquals('/testing/bar', $url);
    // }

    // public function testRelativeUrlWithQueries()
    // {
    //     $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

    //     $url = $this->getGenerator($routes)->generate('testing', array('param1' => 'bar'), UrlGeneratorContract::ABSOLUTE_PATH);

    //     $this->assertEquals('/testing/bar', $url);
    // }

    protected function getGenerator(RouteCollection $routes, array $serverVar = [])
    {
        $server =  [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => '',
            'HTTP_HOST' => 'localhost',
        ];

        $server = array_merge($server, $serverVar);

        return new UrlGenerator($routes, (new ServerRequestFactory())->createServerRequest($server), new UriFactory());
    }

    protected function getRoutes(Route $route)
    {
        $routes = new RouteCollection();
        $routes->add($route);

        return $routes;
    }
}
