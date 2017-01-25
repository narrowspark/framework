<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Extensions\StrExtension;
use Viserio\Component\Support\Str;

class StrExtensionTest extends TestCase
{
    use MockeryTrait;

    protected $customFilters = [
        'camel_case',
        'snake_case',
        'studly_case',
    ];

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testCallback()
    {
        $string = $this->getString();

        $this->assertEquals(Str::class, $string->getCallback());

        $string->setCallback('FooBar');

        $this->assertEquals('FooBar', $string->getCallback());
    }

    public function testName()
    {
        $this->assertInternalType('string', $this->getString()->getName());
    }

    public function testFunctionCallback()
    {
        $mock = $this->mock(Str::class);
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setCallback($mock);

        $this->assertInternalType('array', $string->getFunctions());

        call_user_func($string->getFunctions()[0]->getCallable(), 'foo_bar');
    }

    public function testFunctionIsNotSafe()
    {
        $string   = $this->getString();
        $function = $string->getFunctions()[0];

        $this->assertFalse(in_array('html', $function->getSafe($this->mock('Twig_Node'))));
    }

    public function testCustomFilters()
    {
        $string  = $this->getString();
        $filters = $string->getFilters();

        $this->assertInternalType('array', $filters);

        foreach ($filters as $filter) {
            if (! in_array($filter->getName(), $this->customFilters)) {
                continue;
            }

            $this->assertEquals(Str::class, $filter->getCallable()[0]);
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
