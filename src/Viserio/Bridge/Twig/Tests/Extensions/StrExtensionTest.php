<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Extensions\StrExtension;
use Viserio\Component\Support\Str;

class StrExtensionTest extends MockeryTestCase
{
    protected $customFilters = [
        'camel_case',
        'snake_case',
        'studly_case',
    ];

    public function testCallback()
    {
        $string = $this->getString();

        self::assertEquals(Str::class, $string->getCallback());

        $string->setCallback('FooBar');

        self::assertEquals('FooBar', $string->getCallback());
    }

    public function testName()
    {
        self::assertInternalType('string', $this->getString()->getName());
    }

    public function testFunctionCallback()
    {
        $mock = $this->mock(Str::class);
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setCallback($mock);

        self::assertInternalType('array', $string->getFunctions());

        call_user_func($string->getFunctions()[0]->getCallable(), 'foo_bar');
    }

    public function testFunctionIsNotSafe()
    {
        $string   = $this->getString();
        $function = $string->getFunctions()[0];

        self::assertFalse(in_array('html', $function->getSafe($this->mock('Twig_Node'))));
    }

    public function testCustomFilters()
    {
        $string  = $this->getString();
        $filters = $string->getFilters();

        self::assertInternalType('array', $filters);

        foreach ($filters as $filter) {
            if (! in_array($filter->getName(), $this->customFilters)) {
                continue;
            }

            self::assertEquals(Str::class, $filter->getCallable()[0]);
        }
    }

    public function testWildcardFilters()
    {
        $mock = $this->mock(Str::class);
        $mock->shouldReceive('fooBar')->once();

        $string  = $this->getString();
        $string->setCallback($mock);

        $filters = $string->getFilters();

        foreach ($filters as $filter) {
            if (in_array($filter->getName(), $this->customFilters)) {
                continue;
            }

            call_user_func($filter->getCallable(), 'foo_bar');
        }
    }

    protected function getString()
    {
        return new StrExtension();
    }
}
