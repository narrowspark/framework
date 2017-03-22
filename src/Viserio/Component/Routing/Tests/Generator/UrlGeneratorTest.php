<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Generator;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;

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

        $url = $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('https://localhost/testing', $url);
    }

    public function testAbsoluteUrlWithNonStandardPort()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, ['SERVER_PORT' => 8080])->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('http://localhost:8080/testing', $url);
    }

    public function testAbsoluteSecureUrlWithNonStandardPort()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, ['HTTPS' => 'on', 'SERVER_PORT' => 8080])->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('https://localhost:8080/testing', $url);
    }

    public function testRelativeUrlWithoutParameters()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', []);

        $this->assertEquals('/testing', $url);
    }

    public function testRelativeUrlWithParameter()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['param1' => 'bar']);

        $this->assertEquals('/testing/bar', $url);
    }

    public function testRelativeUrlWithQueries()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['param1' => 'bar']);

        $this->assertEquals('/testing/bar', $url);
    }

    public function testRelativeUrlWithNullParameter()
    {
        $routes = $this->getRoutes((new Route('GET', '/testing.{format}', ['as' => 'testing']))->setParameter('format', null));

        $url = $this->getGenerator($routes)->generate('testing', []);

        $this->assertEquals('/testing', $url);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exceptions\RouteNotFoundException
     * @expectedExceptionMessage Unable to generate a URL for the named route [test] as such route does not exist.
     */
    public function testRelativeUrlWithNullParameterButNotOptional()
    {
        $routes = $this->getRoutes((new Route('GET', '/testing/{foo}/bar', ['as' => 'testing']))->setParameter('foo', null));

        // This must raise an exception because the default requirement for "foo" is "[^/]+" which is not met with these params.
        // Generating path "/testing//bar" would be wrong as matching this route would fail.
        $this->getGenerator($routes)->generate('test', []);
    }

    public function testRelativeUrlWithOptionalZeroParameter()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{page}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['page' => 0]);

        $this->assertEquals('/testing/0', $url);
    }

    public function testNotPassedOptionalParameterInBetween()
    {
        $route = new Route('GET', '/{slug}/{page}', ['as' => 'testing']);
        $route->setParameter('slug', 'index');
        $route->setParameter('page', 0);

        $routes = $this->getRoutes($route);

        $this->assertSame('/index/1', $this->getGenerator($routes)->generate('testing', ['page' => 1]));
        $this->assertSame('/', $this->getGenerator($routes)->generate('testing'));
    }

    public function testRelativeUrlWithExtraParameters()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => 'bar']);

        $this->assertEquals('/testing?foo=bar', $url);
    }

    public function testAbsoluteUrlWithExtraParameters()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => 'bar'], UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('http://localhost/testing?foo=bar', $url);
    }

    public function testUrlWithNullExtraParameters()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => null], UrlGeneratorContract::ABSOLUTE_URL);

        $this->assertEquals('http://localhost/testing', $url);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exceptions\RouteNotFoundException
     * @expectedExceptionMessage Unable to generate a URL for the named route [test] as such route does not exist.
     */
    public function testGenerateWithoutRoutes()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $this->getGenerator($routes)->generate('test', [], UrlGeneratorContract::ABSOLUTE_URL);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exceptions\InvalidParameterException
     */
    public function testRequiredParamAndEmptyPassed()
    {
        $route = new Route('GET', '/{slug}', ['as' => 'testing']);
        $route->setParameter('slug', '.+');
        $routes = $this->getRoutes($route);

        $this->getGenerator($routes)->generate('testing', ['slug' => '']);
    }

    public function testSchemeRequirementDoesNothingIfSameCurrentScheme()
    {
        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'http']));

        $this->assertEquals('/', $this->getGenerator($routes)->generate('testing'));

        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'https']));

        $this->assertEquals('/', $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing'));
    }

    public function testSchemeRequirementForcesAbsoluteUrl()
    {
        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'https']));

        $this->assertEquals('https://localhost/', $this->getGenerator($routes)->generate('testing'));

        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'http']));

        $this->assertEquals('http://localhost/', $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing'));
    }

    public function testPathWithTwoStartingSlashes()
    {
        $routes = $this->getRoutes(new Route('GET', '//path-and-not-domain', ['as' => 'testing']));

        // this must not generate '//path-and-not-domain' because that would be a network path
        $this->assertSame('/path-and-not-domain', $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing', []));
    }

    protected function getGenerator(RouteCollection $routes, array $serverVar = [])
    {
        $server =  [
            'PHP_SELF'    => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS'       => 'off',
            'HTTP_HOST'   => 'localhost',
        ];

        $newServer = array_merge($server, $serverVar);

        return new UrlGenerator($routes, (new ServerRequestFactory())->createServerRequest($newServer), new UriFactory());
    }

    protected function getRoutes(Route $route)
    {
        $routes = new RouteCollection();
        $routes->add($route);

        return $routes;
    }
}
