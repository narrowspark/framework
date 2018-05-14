<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Container\Exception\NotFoundException;

/**
 * @internal
 */
final class ContainerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Container\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function testUnsetRemoveBoundInstances(): void
    {
        $this->container->instance('object', new \stdClass());

        unset($this->container['object']);

        static::assertFalse($this->container->has('object'));

        $this->container->instance('object', new \stdClass());
        $this->container->forget('object');

        static::assertFalse($this->container->has('object'));
    }

    public function testBindingsCanBeOverridden(): void
    {
        $this->container['foo'] = 'bar';
        $foo                    = $this->container['foo'];

        static::assertSame('bar', $foo);

        $this->container['foo'] = 'baz';

        static::assertSame('baz', $this->container['foo']);
    }

    public function testReset(): void
    {
        $this->container->instance('test', 'value');
        $this->container->reset();

        try {
            $this->container->get('test');
            static::fail('this should not happened');
        } catch (NotFoundException $exception) {
            static::assertSame('Abstract [test] is not being managed by the container.', $exception->getMessage());
        }

        static::assertSame([], $this->container->getDefinitions());
    }
}
