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

namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\LazyString;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class LazyStringTest extends TestCase
{
    public function testLazyString(): void
    {
        $count = 0;
        $s = LazyString::fromCallable(function () use (&$count) {
            return (string) ++$count;
        });

        self::assertSame(0, $count);
        self::assertSame('1', (string) $s);
        self::assertSame(1, $count);
    }

    public function testLazyCallable(): void
    {
        $count = 0;
        $s = LazyString::fromCallable([function () use (&$count) {
            return new class($count) {
                private $count;

                public function __construct(int &$count)
                {
                    $this->count = &$count;
                }

                public function __invoke()
                {
                    return (string) ++$this->count;
                }
            };
        }]);

        self::assertSame(0, $count);
        self::assertSame('1', (string) $s);
        self::assertSame(1, $count);
        self::assertSame('1', (string) $s); // ensure the value is memoized
        self::assertSame(1, $count);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReturnTypeError(): void
    {
        $s = LazyString::fromCallable(function () {
            return [];
        });

        self::assertSame((string) $s, 'Return value of ' . __NAMESPACE__ . '\{closure}() passed to ' . LazyString::class . '::fromCallable() must be of the type string, array returned.');
    }
}
