<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Node\Node;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Component\Support\Str;

class StrExtensionTest extends MockeryTestCase
{
    private static $customFilters = [
        'camel_case',
        'snake_case',
        'studly_case',
    ];

    public function testCallback(): void
    {
        $string = $this->getString();

        self::assertEquals(Str::class, $string->getCallback());

        $string->setCallback('FooBar');

        self::assertEquals('FooBar', $string->getCallback());
    }

    public function testName(): void
    {
        self::assertInternalType('string', $this->getString()->getName());
    }

    public function testFunctionCallback(): void
    {
        $mock = $this->mock(Str::class);
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setCallback($mock);

        self::assertInternalType('array', $string->getFunctions());

        \call_user_func($string->getFunctions()[0]->getCallable(), 'foo_bar');
    }

    public function testFunctionIsNotSafe(): void
    {
        $string   = $this->getString();
        $function = $string->getFunctions()[0];

        self::assertFalse(\in_array('html', $function->getSafe($this->mock(Node::class)), true));
    }

    public function testCustomFilters(): void
    {
        $string  = $this->getString();
        $filters = $string->getFilters();

        self::assertInternalType('array', $filters);

        foreach ($filters as $filter) {
            if (! \in_array($filter->getName(), self::$customFilters, true)) {
                continue;
            }

            self::assertEquals(Str::class, $filter->getCallable()[0]);
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

    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();

        $this->allowMockingNonExistentMethods(true);
    }

    protected function getString()
    {
        return new StrExtension();
    }
}
