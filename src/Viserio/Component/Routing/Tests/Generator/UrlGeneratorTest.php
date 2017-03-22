<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Generator;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Generator\UrlGenerator;

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

        $url = $this->getGenerator($routes)->generate('testing', [], UrlGeneratorContract::ABSOLUTE_PATH);

        $this->assertEquals('/testing', $url);
    }

    public function testRelativeUrlWithParameter()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['param1' => 'bar'], UrlGeneratorContract::ABSOLUTE_PATH);

        $this->assertEquals('/testing/bar', $url);
    }

    public function testRelativeUrlWithQueries()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['param1' => 'bar'], UrlGeneratorContract::ABSOLUTE_PATH);

        $this->assertEquals('/testing/bar', $url);
    }

    public function testRelativeUrlWithNullParameter()
    {
        $routes = $this->getRoutes((new Route('GET', '/testing.{format}', ['as' => 'testing']))->setParameter('format', null));

        $url = $this->getGenerator($routes)->generate('testing', [], UrlGeneratorContract::ABSOLUTE_PATH);

        $this->assertEquals('/testing', $url);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testRelativeUrlWithNullParameterButNotOptional()
    {
        $routes = $this->getRoutes((new Route('GET', '/testing/{foo}/bar', ['as' => 'testing']))->setParameter('foo', null));

        // This must raise an exception because the default requirement for "foo" is "[^/]+" which is not met with these params.
        // Generating path "/testing//bar" would be wrong as matching this route would fail.
        $this->getGenerator($routes)->generate('test', [], UrlGeneratorContract::ABSOLUTE_PATH);
    }

    public function testRelativeUrlWithOptionalZeroParameter()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{page}', ['as' =>'testing']));

        $this->getGenerator($routes)->generate('test', ['page' => 0], UrlGeneratorContract::ABSOLUTE_PATH);

        $this->assertEquals('/testing/0', $url);
    }

    public function testNotPassedOptionalParameterInBetween()
    {
        $route = new Route('GET', '/{slug}/{page}', ['as' => 'testing']);
        $route->setParameter('slug', 'index')
            ->setParameter('page', 0);
        $routes = $this->getRoutes($route);

        $this->assertSame('/index/1', $this->getGenerator($routes)->generate('testing', ['page' => 1]));
        $this->assertSame('/', $this->getGenerator($routes)->generate('testing'));
    }

    public function testRelativeUrlWithExtraParameters()
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => 'bar'], UrlGeneratorContract::ABSOLUTE_PATH);

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

        $this->assertEquals('/', $this->getGenerator($routes, ['scheme' => 'https'])->generate('testing'));
    }

    public function testSchemeRequirementForcesAbsoluteUrl()
    {
        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'https']));

        $this->assertEquals('https://localhost/', $this->getGenerator($routes)->generate('testing'));

        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'http']));

        $this->assertEquals('http://localhost/', $this->getGenerator($routes, ['scheme' => 'https'])->generate('testing'));
    }

    public function testPathWithTwoStartingSlashes()
    {
        $routes = $this->getRoutes('test', new Route('GET', '//path-and-not-domain', ['as' => 'testing']));

        // this must not generate '//path-and-not-domain' because that would be a network path
        $this->assertSame('/path-and-not-domain', $this->getGenerator($routes, ['BaseUrl' => ''])->generate('testing'));
    }

    protected function getGenerator(RouteCollection $routes, array $serverVar = [])
    {
        $server =  [
            'PHP_SELF'    => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS'       => '',
            'HTTP_HOST'   => 'localhost',
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
