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
    public function testAbsoluteUrlWithPort80(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        self::assertEquals('http://localhost/testing', $url);
    }

    public function testAbsoluteSecureUrlWithPort443(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        self::assertEquals('https://localhost/testing', $url);
    }

    public function testAbsoluteUrlWithNonStandardPort(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, ['SERVER_PORT' => 8080])->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        self::assertEquals('http://localhost:8080/testing', $url);
    }

    public function testAbsoluteSecureUrlWithNonStandardPort(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes, ['HTTPS' => 'on', 'SERVER_PORT' => 8080])->generate('testing', [], UrlGeneratorContract::ABSOLUTE_URL);

        self::assertEquals('https://localhost:8080/testing', $url);
    }

    public function testRelativeUrlWithoutParameters(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', []);

        self::assertEquals('/testing', $url);
    }

    public function testRelativeUrlWithParameter(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['param1' => 'bar']);

        self::assertEquals('/testing/bar', $url);
    }

    public function testRelativeUrlWithQueries(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{param1}', ['as' =>'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['param1' => 'bar']);

        self::assertEquals('/testing/bar', $url);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exception\RouteNotFoundException
     * @expectedExceptionMessage Unable to generate a URL for the named/action route [test] as such route does not exist.
     */
    public function testThrowExceptionOnNotFoundRoute(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $this->getGenerator($routes)->generate('test');
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exception\UrlGenerationException
     * @expectedExceptionMessage Missing required parameters for [Route: testing] [URI: /testing/{foo}/bar].
     */
    public function testRelativeUrlWithNotOptionalParameter(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing/{foo}/bar', ['as' => 'testing']));

        // This must raise an exception because the default requirement for "foo" is "[^/]+" which is not met with these params.
        // Generating path "/testing//bar" would be wrong as matching this route would fail.
        $this->getGenerator($routes)->generate('testing');
    }

    public function testRelativeUrlWithExtraParameters(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => 'bar']);

        self::assertEquals('/testing?foo=bar', $url);
    }

    public function testAbsoluteUrlWithExtraParameters(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => 'bar'], UrlGeneratorContract::ABSOLUTE_URL);

        self::assertEquals('http://localhost/testing?foo=bar', $url);
    }

    public function testUrlWithNullExtraParameters(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $url = $this->getGenerator($routes)->generate('testing', ['foo' => null], UrlGeneratorContract::ABSOLUTE_URL);

        self::assertEquals('http://localhost/testing', $url);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exception\RouteNotFoundException
     * @expectedExceptionMessage Unable to generate a URL for the named/action route [test] as such route does not exist.
     */
    public function testGenerateWithoutRoutes(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/testing', ['as' => 'testing']));

        $this->getGenerator($routes)->generate('test', [], UrlGeneratorContract::ABSOLUTE_URL);
    }

    public function testSchemeRequirementDoesNothingIfSameCurrentScheme(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'http']));

        self::assertEquals('/', $this->getGenerator($routes)->generate('testing'));

        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'https']));

        self::assertEquals('/', $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing'));
    }

    public function testSchemeRequirementForcesAbsoluteUrl(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'https']));

        self::assertEquals('https://localhost/', $this->getGenerator($routes)->generate('testing'));

        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'testing', 'http']));

        self::assertEquals('http://localhost/', $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing'));
    }

    public function testPathWithTwoStartingSlashes(): void
    {
        $routes = $this->getRoutes(new Route('GET', '//path-and-not-domain', ['as' => 'testing']));

        // this must not generate '//path-and-not-domain' because that would be a network path
        self::assertSame('/path-and-not-domain', $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('testing'));
    }

    public function testNoTrailingSlashForMultipleOptionalParameters(): void
    {
        $route = new Route('GET', '/category/{slug1}/{slug2}/{slug3}', ['as' => 'testing']);
        $route->addParameter('slug2', null)->addParameter('slug3', null);

        $routes = $this->getRoutes($route);

        self::assertEquals('/category/foo', $this->getGenerator($routes)->generate('testing', ['slug1' => 'foo']));
    }

    public function testWithAnIntegerAsADefaultValue(): void
    {
        $route = new Route('GET', '/{default}', ['as' => 'testing']);
        $route->addParameter('default', 0);

        $routes = $this->getRoutes($route);

        self::assertEquals('/foo', $this->getGenerator($routes)->generate('testing', ['default' => 'foo']));
    }

    public function testNullForOptionalParameterIsIgnored(): void
    {
        $route = new Route('GET', '/test/{default}', ['as' => 'testing']);
        $route->addParameter('default', 0);

        $routes = $this->getRoutes($route);

        self::assertEquals('/test', $this->getGenerator($routes)->generate('testing', ['default' => null]));
    }

    public function testWithRouteDomain(): void
    {
        $route = new Route('GET', '/foo', ['as' => 'testing', 'domain' => 'test.de', 'https']);

        $routes = $this->getRoutes($route);

        self::assertEquals('https://test.de/foo', $this->getGenerator($routes)->generate('testing'));
    }

    public function testQueryParamSameAsDefault(): void
    {
        $route = new Route('GET', '/test', ['as' => 'testing']);
        $route->addParameter('page', 1);

        $routes = $this->getRoutes($route);

        self::assertSame('/test?page=2', $this->getGenerator($routes)->generate('testing', ['page' => 2]));
        self::assertSame('/test?page=3', $this->getGenerator($routes)->generate('testing', ['page' => 3]));
        self::assertSame('/test?page=3', $this->getGenerator($routes)->generate('testing', ['page' => '3']));
        self::assertSame('/test?page=1', $this->getGenerator($routes)->generate('testing'));
    }

    public function testArrayQueryParamSameAsDefault(): void
    {
        $route = new Route('GET', '/test', ['as' => 'testing']);
        $route->addParameter('array', ['foo', 'bar']);

        $routes = $this->getRoutes($route);

        self::assertSame('/test?array%5B0%5D=bar&array%5B1%5D=foo', $this->getGenerator($routes)->generate('testing', ['array' => ['bar', 'foo']]));
        self::assertSame('/test?array%5Ba%5D=foo&array%5Bb%5D=bar', $this->getGenerator($routes)->generate('testing', ['array' => ['a' => 'foo', 'b' => 'bar']]));
        self::assertSame('/test?array%5B0%5D=foo&array%5B1%5D=bar', $this->getGenerator($routes)->generate('testing', ['array' => ['foo', 'bar']]));
        self::assertSame('/test?array%5B1%5D=bar&array%5B0%5D=foo', $this->getGenerator($routes)->generate('testing', ['array' => [1 => 'bar', 0 => 'foo']]));
        self::assertSame('/test?array%5B0%5D=foo&array%5B1%5D=bar', $this->getGenerator($routes)->generate('testing'));
    }

    public function testGenerateWithSpecialRouteName(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/bar', ['as' => '$péß^a|']));

        self::assertSame('/bar', $this->getGenerator($routes)->generate('$péß^a|'));
    }

    public function testEncodingOfRelativePathSegments(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/dir/../dir/..', ['as' => 'test']));

        self::assertSame('/dir/%2E%2E/dir/%2E%2E', $this->getGenerator($routes)->generate('test'));

        $routes = $this->getRoutes(new Route('GET', '/dir/./dir/.', ['as' => 'test']));

        self::assertSame('/dir/%2E/dir/%2E', $this->getGenerator($routes)->generate('test'));

        $routes = $this->getRoutes(new Route('GET', '/a./.a/a../..a/...', ['as' => 'test']));

        self::assertSame('/a./.a/a../..a/...', $this->getGenerator($routes)->generate('test'));
    }

    public function testVariableWithNoRealSeparator(): void
    {
        $route = new Route('GET', '/get{what}', ['as' => 'test']);
        $route->addParameter('what', 'All');

        $routes    = $this->getRoutes($route);
        $generator = $this->getGenerator($routes);

        self::assertSame('/getAll', $generator->generate('test'));
        self::assertSame('/getSites', $generator->generate('test', ['what' => 'Sites']));
    }

    /**
     * @dataProvider provideRelativePaths
     *
     * @param mixed $sourcePath
     * @param mixed $targetPath
     * @param mixed $expectedPath
     */
    public function testGetRelativePath($sourcePath, $targetPath, $expectedPath): void
    {
        self::assertSame($expectedPath, UrlGenerator::getRelativePath($sourcePath, $targetPath));
    }

    public function provideRelativePaths()
    {
        return [
            [
                '/same/dir/',
                '/same/dir/',
                '',
            ],
            [
                '/same/file',
                '/same/file',
                '',
            ],
            [
                '/',
                '/file',
                'file',
            ],
            [
                '/',
                '/dir/file',
                'dir/file',
            ],
            [
                '/dir/file.html',
                '/dir/different-file.html',
                'different-file.html',
            ],
            [
                '/same/dir/extra-file',
                '/same/dir/',
                './',
            ],
            [
                '/parent/dir/',
                '/parent/',
                '../',
            ],
            [
                '/parent/dir/extra-file',
                '/parent/',
                '../',
            ],
            [
                '/a/b/',
                '/x/y/z/',
                '../../x/y/z/',
            ],
            [
                '/a/b/c/d/e',
                '/a/c/d',
                '../../../c/d',
            ],
            [
                '/a/b/c//',
                '/a/b/c/',
                '../',
            ],
            [
                '/a/b/c/',
                '/a/b/c//',
                './/',
            ],
            [
                '/root/a/b/c/',
                '/root/x/b/c/',
                '../../../x/b/c/',
            ],
            [
                '/a/b/c/d/',
                '/a',
                '../../../../a',
            ],
            [
                '/special-chars/sp%20ce/1€/mäh/e=mc²',
                '/special-chars/sp%20ce/1€/<µ>/e=mc²',
                '../<µ>/e=mc²',
            ],
            [
                'not-rooted',
                'dir/file',
                'dir/file',
            ],
            [
                '//dir/',
                '',
                '../../',
            ],
            [
                '/dir/',
                '/dir/file:with-colon',
                './file:with-colon',
            ],
            [
                '/dir/',
                '/dir/subdir/file:with-colon',
                'subdir/file:with-colon',
            ],
            [
                '/dir/',
                '/dir/:subdir/',
                './:subdir/',
            ],
        ];
    }

    public function testGenerateNetworkPath(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/{name}', ['as' => 'test', 'domain' => 'fr.example.com', 'http']));

        self::assertSame(
            '//fr.example.com/Narrow',
            $this->getGenerator($routes)->generate('test', ['name' => 'Narrow'], UrlGeneratorContract::NETWORK_PATH),
            'network path with different host'
        );

        self::assertSame(
            '//fr.example.com/Narrow?query=string',
            $this->getGenerator($routes)->generate('test', ['name' => 'Narrow', 'query' => 'string'], UrlGeneratorContract::NETWORK_PATH),
            'network path although host same as context'
        );

        self::assertSame(
            'http://fr.example.com/Narrow',
            $this->getGenerator($routes, ['HTTPS' => 'on'])->generate('test', ['name' => 'Narrow'], UrlGeneratorContract::NETWORK_PATH),
            'absolute URL because scheme requirement does not match route scheme'
        );

        self::assertSame(
            'http://fr.example.com/Narrow',
            $this->getGenerator($routes)->generate('test', ['name' => 'Narrow'], UrlGeneratorContract::ABSOLUTE_URL),
            'absolute URL with same scheme because it is requested'
        );
    }

    public function testFindRouteOnAction(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/', ['as' => 'test', 'controller' => 'Home@index']));

        self::assertSame('/', $this->getGenerator($routes)->generate('Home@index'));
    }

    public function testFragmentUrl(): void
    {
        $routes = $this->getRoutes(new Route('GET', '/index#test', ['as' => 'test']));

        self::assertSame('/index#test', $this->getGenerator($routes)->generate('test'));
        self::assertSame('/index?1#test', $this->getGenerator($routes)->generate('test', [1]));
        self::assertSame('/index?baz=foo#test', $this->getGenerator($routes)->generate('test', ['baz' => 'foo']));
        self::assertSame('/index?baz=%C3%A5%CE%B1%D1%84#test', $this->getGenerator($routes)->generate('test', ['baz' => 'åαф']));

        // Do not escape valid characters
        $routes = $this->getRoutes(new Route('GET', '/index#?', ['as' => 'test']));

        self::assertSame('/index#?', $this->getGenerator($routes)->generate('test'));
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

        $newServer = \array_merge($server, $serverVar);

        return new UrlGenerator($routes, (new ServerRequestFactory())->createServerRequestFromArray($newServer), new UriFactory());
    }

    protected function getRoutes(Route $route)
    {
        $routes = new RouteCollection();
        $routes->add($route);

        return $routes;
    }
}
