<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Tests\Unit\Helper;

use BTest;
use Error;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ReflectionClass;
use stdClass;
use Test;
use Viserio\Component\Container\Helper\Reflection;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\LogicException;

require __DIR__ . '/../../Fixture/Reflection/ExpandClassNoNamespace.php';

require __DIR__ . '/../../Fixture/Reflection/ExpandClassInBracketedNamespace.php';

require __DIR__ . '/../../Fixture/Reflection/ExpandClassInNamespace.php';

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Helper\Reflection
 *
 * @small
 */
final class ReflectionTest extends MockeryTestCase
{
    /** @var ReflectionClass */
    private $rcTest;

    /** @var ReflectionClass */
    private $rcBTest;

    /** @var ReflectionClass */
    private $rcFoo;

    /** @var ReflectionClass */
    private $rcBar;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rcTest = new ReflectionClass(Test::class);
        $this->rcBTest = new ReflectionClass(BTest::class);
        $this->rcFoo = new ReflectionClass(\Test\Space\Foo::class);
        $this->rcBar = new ReflectionClass(\Test\Space\Bar::class);
    }

    public function testThrowExceptionOnNewInstance(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Class [' . Reflection::class . '] is static and cannot be instantiated.');

        new Reflection();
    }

    public function testExpandClassNameThrowExceptionOnEmptyClassName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name cant be empty.');

        Reflection::expandClassName('', new ReflectionClass(stdClass::class));
    }

    /**
     * @dataProvider provideBuildinTypes
     */
    public function testExpandClassNameWithBuiltInTypes(string $type): void
    {
        self::assertSame(\strtolower($type), Reflection::expandClassName($type, new ReflectionClass(stdClass::class)));
    }

    public function provideBuildInTypes(): iterable
    {
        return [
            ['string'],
            ['int'],
            ['float'],
            ['bool'],
            ['array'],
            ['object'],
            ['callable'],
            ['iterable'],
            ['void'],
            ['null'],
            ['String'],
        ];
    }

    public function testExpandClassNameThrowExceptionOnAnonymousClasses(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Anonymous classes are not supported.');

        Reflection::expandClassName('A', new ReflectionClass(new class() {
        }));
    }

    public function testExpandClassWithClassAndNamespace(): void
    {
        self::assertSame('A', Reflection::expandClassName('A', $this->rcTest));
        self::assertSame('A\B', Reflection::expandClassName('C', $this->rcTest));
        self::assertSame('BTest', Reflection::expandClassName('BTest', $this->rcBTest));
        self::assertSame('Test\Space\Foo', Reflection::expandClassName('self', $this->rcFoo));
        self::assertSame('Test\Space\Foo', Reflection::expandClassName('Self', $this->rcFoo));
    }

    /**
     * @dataProvider provideExpandClassWithAliasClassesCases
     */
    public function testExpandClassWithAliasClasses(string $alias, string $expected1, string $expected2): void
    {
        self::assertSame($expected1, Reflection::expandClassName($alias, $this->rcFoo));
        self::assertSame($expected2, Reflection::expandClassName($alias, $this->rcBar));
    }

    public static function provideExpandClassWithAliasClassesCases(): iterable
    {
        return [
            [
                '\Absolute',
                'Absolute',
                'Absolute',
            ],
            [
                '\Absolute\Foo',
                'Absolute\Foo',
                'Absolute\Foo',
            ],
            [
                'AAA',
                'Test\Space\AAA',
                'AAA',
            ],
            [
                'AAA\Foo',
                'Test\Space\AAA\Foo',
                'AAA\Foo',
            ],
            [
                'B',
                'Test\Space\B',
                'BBB',
            ],
            [
                'B\Foo',
                'Test\Space\B\Foo',
                'BBB\Foo',
            ],
            [
                'DDD',
                'Test\Space\DDD',
                'CCC\DDD',
            ],
            [
                'DDD\Foo',
                'Test\Space\DDD\Foo',
                'CCC\DDD\Foo',
            ],
            [
                'F',
                'Test\Space\F',
                'EEE\FFF',
            ],
            [
                'F\Foo',
                'Test\Space\F\Foo',
                'EEE\FFF\Foo',
            ],
            [
                'HHH',
                'Test\Space\HHH',
                'Test\Space\HHH',
            ],
            [
                'Notdef',
                'Test\Space\Notdef',
                'Test\Space\Notdef',
            ],
            [
                'Notdef\Foo',
                'Test\Space\Notdef\Foo',
                'Test\Space\Notdef\Foo',
            ],
            // trim leading backslash
            [
                'G',
                'Test\Space\G',
                'GGG',
            ],
            [
                'G\Foo',
                'Test\Space\G\Foo',
                'GGG\Foo',
            ],
        ];
    }

    public function testGetUseStatements(): void
    {
        self::assertSame(
            ['C' => 'A\B'],
            Reflection::getUseStatements(new ReflectionClass('Test'))
        );
        self::assertSame(
            [],
            Reflection::getUseStatements(new ReflectionClass('Test\Space\Foo'))
        );
        self::assertSame(
            ['AAA' => 'AAA', 'B' => 'BBB', 'DDD' => 'CCC\DDD', 'F' => 'EEE\FFF', 'G' => 'GGG'],
            Reflection::getUseStatements(new ReflectionClass('Test\Space\Bar'))
        );
        self::assertSame(
            [],
            Reflection::getUseStatements(new ReflectionClass('stdClass'))
        );
    }

    public function testGetUseStatementsThrowExceptionOnAnonymousClasses(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Anonymous classes are not supported.');

        Reflection::getUseStatements(new ReflectionClass(new class() {
        }));
    }
}
