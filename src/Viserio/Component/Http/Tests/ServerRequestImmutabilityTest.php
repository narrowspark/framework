<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Tests\Fixture\MutableObject;

/**
 * Ported from psr7immutability
 *
 * @link https://github.com/pmjones/psr7immutability
 */
class ServerRequestImmutabilityTest extends TestCase
{
    private $request;

    public function setUp()
    {
        $this->request = new ServerRequest('', 'GET');
    }

    public function testWithAttribute_object()
    {
        $request = $this->request->withAttribute(
            'testObject',
            new MutableObject('foo')
        );

        // value should still be foo
        $this->assertSame(
            'foo',
            $request->getAttribute('testObject')->get()
        );

        // try to mutate the value
        $request->getAttribute('testObject')->set('bar');

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            $request->getAttribute('testObject')->get(),
            'withAttribute() not implemented properly (object attributes are mutable)'
        );
    }

    public function testWithAttribute_arrayContainsObject()
    {
        $request = $this->request->withAttribute(
            'testArray',
            [
                'testObject' => new MutableObject('foo'),
            ]
        );

        // value should still be 'foo'
        $this->assertSame(
            'foo',
            $request->getAttribute('testArray')['testObject']->get()
        );

        // try to mutate the value
        $request->getAttribute('testArray')['testObject']->set('bar');

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            $request->getAttribute('testArray')['testObject']->get(),
            'withAttribute() not implemented properly (objects in array attributes are mutable)'
        );
    }

    public function testWithAttribute_arrayContainsReference()
    {
        $testRef = 'foo';
        $request = $this->request->withAttribute(
            'testArray',
            [
                'testRef' => &$testRef
            ]
        );

        // value should still be 'foo'
        $this->assertSame(
            'foo',
            $request->getAttribute('testArray')['testRef']
        );

        // try to mutate the value
        $request->getAttribute('testArray')['testRef'] = 'bar';

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            $request->getAttribute('testArray')['testRef'],
            'withAttribute() not implemented properly (references in array attributes are mutable)'
        );
    }

    public function testWithAttribute_resource()
    {
        $testResource = fopen('php://temp', 'w+');
        fwrite($testResource, 'foo');
        rewind($testResource);
        $request = $this->request->withAttribute(
            'testResource',
            $testResource
        );

        // value should still be 'foo'
        $this->assertSame(
            'foo',
            fgets($request->getAttribute('testResource'))
        );

        // try to mutate the value
        rewind($request->getAttribute('testResource'));
        fwrite($request->getAttribute('testResource'), 'bar');
        rewind($request->getAttribute('testResource'));

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            fgets($request->getAttribute('testResource')),
            'withAttribute() not implemented properly (resource attributes are mutable)'
        );
    }

    public function testWithParsedBody_object()
    {
        $request = $this->request->withParsedBody(
            new MutableObject('foo')
        );

        // value should still be foo
        $this->assertSame(
            'foo',
            $request->getParsedBody()->get()
        );

        // try to mutate the value
        $request->getParsedBody()->set('bar');

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            $request->getParsedBody()->get(),
            'withParsedBody() not implemented properly (parsed body objects are mutable)'
        );
    }

    public function testWithParsedBody_arrayContainsObject()
    {
        $request = $this->request->withParsedBody([
            'testObject' => new MutableObject('foo'),
        ]);

        // value should still be 'foo'
        $this->assertSame(
            'foo',
            $request->getParsedBody()['testObject']->get()
        );

        // try to mutate the value
        $request->getParsedBody()['testObject']->set('bar');

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            $request->getParsedBody()['testObject']->get(),
            'withParsedBody() not implemented properly (objects in parsed body arrays are mutable)'
        );
    }

    public function testWithParsedBody_arrayContainsReference()
    {
        $testRef = 'foo';
        $request = $this->request->withParsedBody([
            'testRef' => &$testRef
        ]);

        // value should still be 'foo'
        $this->assertSame(
            'foo',
            $request->getParsedBody()['testRef']
        );

        // try to mutate the value
        $request->getParsedBody()['testRef'] = 'bar';

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            $request->getParsedBody()['testRef'],
            'withParsedBody() not implemented properly (references in parsed body arrays are mutable)'
        );
    }

    public function testWithParsedBody_arrayContainsResource()
    {
        $testResource = fopen('php://temp', 'w+');
        fwrite($testResource, 'foo');
        rewind($testResource);
        $request = $this->request->withParsedBody([
            'testResource' => $testResource,
        ]);

        // value should still be 'foo'
        $this->assertSame(
            'foo',
            fgets($request->getParsedBody()['testResource'])
        );

        // try to mutate the value
        rewind($request->getParsedBody()['testResource']);
        fwrite($request->getParsedBody()['testResource'], 'bar');
        rewind($request->getParsedBody()['testResource']);

        // if implementation enforces immutability,
        // the value should not have changed
        $this->assertSame(
            'foo',
            fgets($request->getParsedBody()['testResource']),
            'withParsedBody() not implemented properly (resources in parsed body arrays are mutable)'
        );
    }
}
