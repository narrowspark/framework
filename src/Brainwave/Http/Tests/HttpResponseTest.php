<?php

namespace Brainwave\Http\Test;

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

use Brainwave\Contracts\Support\Jsonable;
use Brainwave\Http\RedirectResponse;
use Brainwave\Http\Request;
use Brainwave\Http\Response;
use Mockery as Mock;

/**
 * HttpResponseTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testJsonResponsesAreConvertedAndHeadersAreSet()
    {
        $response = new Response(new \Brainwave\Http\Test\JsonableStub());
        $this->assertEquals('foo', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $response = new Response();
        $response->setContent(['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testRenderablesAreRendered()
    {
        $mock = Mock::mock('Brainwave\Contracts\Support\Renderable');
        $mock->shouldReceive('render')->once()->andReturn('foo');

        $response = new Response($mock);
        $this->assertEquals('foo', $response->getContent());
    }

    public function testHeader()
    {
        $response = new Response();
        $this->assertNull($response->headers->get('foo'));
        $response->headers('foo', 'bar');
        $this->assertEquals('bar', $response->headers->get('foo'));
        $response->headers('foo', 'baz', false);
        $this->assertEquals('bar', $response->headers->get('foo'));
        $response->headers('foo', 'baz');
        $this->assertEquals('baz', $response->headers->get('foo'));
    }

    public function testWithCookie()
    {
        $response = new Response();
        $this->assertEquals(0, count($response->headers->getCookies()));
        $this->assertEquals($response, $response->withCookie(new \Symfony\Component\HttpFoundation\Cookie('foo', 'bar')));
        $cookies = $response->headers->getCookies();
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('foo', $cookies[0]->getName());
        $this->assertEquals('bar', $cookies[0]->getValue());
    }

    public function testGetOriginalContent()
    {
        $arr = ['foo' => 'bar'];
        $response = new Response();
        $response->setContent($arr);
        $this->assertSame($arr, $response->getOriginalContent());
    }

    public function testSetAndRetrieveStatusCode()
    {
        $response = new Response('foo');
        $response->setStatusCode(404);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testOnlyInputOnRedirect()
    {
        $response = new RedirectResponse('foo.bar');
        $response->setRequest(Request::create('/', 'GET', ['name' => 'Narrowspark', 'age' => 1]));
        $response->setSession($session = Mock::mock('Brainwave\Session\Store'));
        $session->shouldReceive('flashInput')->once()->with(['name' => 'Narrowspark']);
        $response->onlyInput('name');
    }

    public function testExceptInputOnRedirect()
    {
        $response = new RedirectResponse('foo.bar');
        $response->setRequest(Request::create('/', 'GET', ['name' => 'Narrowspark', 'age' => 1]));
        $response->setSession($session = Mock::mock('Brainwave\Session\Store'));
        $session->shouldReceive('flashInput')->once()->with(['name' => 'Narrowspark']);
        $response->exceptInput('age');
    }

    public function testSettersGettersOnRequest()
    {
        $response = new RedirectResponse('foo.bar');
        $this->assertNull($response->getRequest());
        $this->assertNull($response->getSession());
        $request = Request::create('/', 'GET');
        $session = Mock::mock('Brainwave\Session\Store');
        $response->setRequest($request);
        $response->setSession($session);
        $this->assertSame($request, $response->getRequest());
        $this->assertSame($session, $response->getSession());
    }

    public function testMagicCall()
    {
        $response = new RedirectResponse('foo.bar');
        $response->setRequest(Request::create('/', 'GET', ['name' => 'Narrowspark', 'age' => 1]));
        $response->setSession($session = Mock::mock('Brainwave\Session\Store'));
        $session->shouldReceive('flash')->once()->with('foo', 'bar');
        $response->withFoo('bar');
    }

    public function testMagicCallException()
    {
        $this->setExpectedException('BadMethodCallException');
        $response = new RedirectResponse('foo.bar');
        $response->doesNotExist('bar');
    }
}

class JsonableStub implements Jsonable
{
    public function toJson($options = 0)
    {
        return 'foo';
    }
}
