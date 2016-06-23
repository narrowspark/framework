<?php
namespace Viserio\Routing\Tests\UrlGenerator;

use Symfony\Component\HttpFoundation\Request;
use Viserio\Routing\UrlGenerator\SimpleUrlGenerator;

class SimpleUrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic functionality.
     */
    public function testFunctionality()
    {
        $generator = $this->getGenerator();
        $this->assertEquals('/', $generator->generate('home'));
    }

    /**
     * Test base URL functionality.
     */
    public function testBaseUrl()
    {
        $generator = $this->getGenerator();
        $request = Request::create('https://www.example.com/subdirectory/somepage', 'GET', [], [], [], [
            'SCRIPT_FILENAME' => 'index.php',
            'PHP_SELF' => '/subdirectory/index.php',
        ]);
        $generator->setRequest($request);
        $this->assertEquals('/subdirectory/', $generator->generate('home'));
    }

    /**
     * Test absolute URL functionality.
     */
    public function testAbsoluteUrl()
    {
        $generator = $this->getGenerator();
        $request = Request::create('https://www.example.com/subdirectory/somepage', 'GET', [], [], [], [
            'SCRIPT_FILENAME' => 'index.php',
            'PHP_SELF' => '/subdirectory/index.php',
        ]);
        $generator->setRequest($request);
        $this->assertEquals('https://www.example.com/subdirectory/', $generator->generate('home', [], true));
    }

    /**
     * Test a dynamic route.
     */
    public function testDynamicRoute()
    {
        $generator = $this->getGenerator([
            'user_edit' => [
                'params' => [
                    'id',
                ],
                'path' => '/user/{id}/edit',
            ],
        ]);
        $this->assertEquals('/user/123/edit', $generator->generate('user_edit', ['id' => 123]));
    }

    /**
     * Test a dynamic route with a missing parameter.
     */
    public function testDynamicRouteWithMissingParameter()
    {
        $generator = $this->getGenerator([
            'user_edit' => [
                'params' => [
                    'id',
                ],
                'path' => '/user/{id}/edit',
            ],
        ]);
        $this->setExpectedException('RuntimeException', 'Missing required parameter');
        $this->assertEquals('/user/123/edit', $generator->generate('user_edit'));
    }

    private function getGenerator(array $routes = ['home' => '/'])
    {
        $dataGenerator = $this->getMockForAbstractClass('Viserio\Contracts\Routing\DataGenerator');
        $dataGenerator->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($routes));

        return new SimpleUrlGenerator($dataGenerator);
    }
}
