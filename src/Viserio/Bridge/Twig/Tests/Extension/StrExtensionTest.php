<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Node\Node;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Component\Support\Str;

/**
 * @internal
 *
 * @small
 */
final class StrExtensionTest extends MockeryTestCase
{
    /** @var array */
    private static $customFilters = [
        'camel_case',
        'snake_case',
        'studly_case',
    ];

    /** @var \Mockery\MockInterface|\Viserio\Component\Support\Str */
    private $stringMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stringMock = \Mockery::mock(Str::class);
    }

    public function testCallback(): void
    {
        $string = $this->getString();

        self::assertEquals(Str::class, $string->getStaticClassName());

        $string->setStaticClassName('FooBar');

        self::assertEquals('FooBar', $string->getStaticClassName());
    }

    public function testName(): void
    {
        self::assertSame('Viserio_Bridge_Twig_Extension_String', $this->getString()->getName());
    }

    public function testFunctionCallback(): void
    {
        $mock = $this->stringMock;
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setStaticClassName($mock);

        \call_user_func($string->getFunctions()[0]->getCallable(), 'foo_bar');
    }

    public function testFunctionIsNotSafe(): void
    {
        $string = $this->getString();
        $function = $string->getFunctions()[0];

        self::assertNotContains('html', $function->getSafe(\Mockery::mock(Node::class)));
    }

    public function testCustomFilters(): void
    {
        $string = $this->getString();

        self::assertCount(1, $string->getFilters());
    }

    public function testWildcardFilters(): void
    {
        $mock = $this->stringMock;
        $mock->shouldReceive('fooBar')
            ->once();

        $string = $this->getString();
        $string->setStaticClassName($mock);

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
