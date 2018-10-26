<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Node\Node;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Component\Support\Str;

/**
 * @internal
 */
final class StrExtensionTest extends MockeryTestCase
{
    private static $customFilters = [
        'camel_case',
        'snake_case',
        'studly_case',
    ];

    public function testCallback(): void
    {
        $string = $this->getString();

        $this->assertEquals(Str::class, $string->getCallback());

        $string->setCallback('FooBar');

        $this->assertEquals('FooBar', $string->getCallback());
    }

    public function testName(): void
    {
        $this->assertInternalType('string', $this->getString()->getName());
    }

    public function testFunctionCallback(): void
    {
        $mock = $this->mock(Str::class);
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setCallback($mock);

        $this->assertInternalType('array', $string->getFunctions());

        \call_user_func($string->getFunctions()[0]->getCallable(), 'foo_bar');
    }

    public function testFunctionIsNotSafe(): void
    {
        $string   = $this->getString();
        $function = $string->getFunctions()[0];

        $this->assertNotContains('html', $function->getSafe($this->mock(Node::class)));
    }

    public function testCustomFilters(): void
    {
        $string  = $this->getString();
        $filters = $string->getFilters();

        $this->assertInternalType('array', $filters);

        foreach ($filters as $filter) {
            if (! \in_array($filter->getName(), self::$customFilters, true)) {
                continue;
            }

            $this->assertEquals(Str::class, $filter->getCallable()[0]);
        }
    }

    public function testWildcardFilters(): void
    {
        $mock = $this->mock(Str::class);
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setCallback($mock);

        $filters = $string->getFilters();

        foreach ($filters as $filter) {
            if (\in_array($filter->getName(), self::$customFilters, true)) {
                continue;
            }

            \call_user_func($filter->getCallable(), 'foo_bar');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    /**
     * @return StrExtension
     */
    protected function getString(): StrExtension
    {
        return new StrExtension();
    }
}
