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

namespace Viserio\Component\Finder\Tests;

use PHPUnit\Framework\TestCase;
use function Viserio\Component\Finder\glob;

/**
 * @covers \Viserio\Component\Finder\Iterator\GlobIterator
 *
 * @internal
 *
 * @small
 */
final class HelperTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestStreamWrapper::register('globtest', __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Iterator');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        TestStreamWrapper::unregister('globtest');
    }

    /**
     * @return iterable<array<array<string>|string>>
     */
    public static function provideGlobStreamWrapperCases(): iterable
    {
        yield [
            'globtest:///*.css',
            [
                'globtest:///base.css',
            ],
        ];

        yield [
            'globtest:///*css*',
            [
                'globtest:///base.css',
                'globtest:///css',
            ],
        ];

        yield [
            'globtest:///*/*.css',
            [
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
            ],
        ];

        yield [
            'globtest:///*/*.c?s',
            [
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
                'globtest:///css/style.cts',
                'globtest:///css/style.cxs',
            ],
        ];

        yield [
            'globtest:///*/*.c[st]s',
            [
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
                'globtest:///css/style.cts',
            ],
        ];

        yield [
            'globtest:///*/*.c[t]s',
            [
                'globtest:///css/style.cts',
            ],
        ];

        yield [
            'globtest:///*/*.c[t-x]s',
            [
                'globtest:///css/style.cts',
                'globtest:///css/style.cxs',
            ],
        ];

        yield [
            'globtest:///*/*.c[^s]s',
            [
                'globtest:///css/style.cts',
                'globtest:///css/style.cxs',
            ],
        ];

        yield [
            'globtest:///*/*.c[^t-x]s',
            [
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
            ],
        ];

        yield [
            'globtest:///*/**/*.css',
            [
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
            ],
        ];

        yield [
            'globtest:///**/*.css',
            [
                'globtest:///base.css',
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
            ],
        ];

        yield [
            'globtest:///**/*css',
            [
                'globtest:///base.css',
                'globtest:///css',
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
            ],
        ];

        yield [
            'globtest:///**/{base,reset}.css',
            [
                'globtest:///base.css',
                'globtest:///css/reset.css',
            ],
        ];

        yield [
            'globtest:///css{,/**/*}',
            [
                'globtest:///css',
                'globtest:///css/reset.css',
                'globtest:///css/style.css',
                'globtest:///css/style.cts',
                'globtest:///css/style.cxs',
            ],
        ];

        yield [
            'globtest:///*foo*',
            [],
        ];
    }

    /**
     * @dataProvider provideGlobStreamWrapperCases
     *
     * @param string[] $expected
     */
    public function testGlobStreamWrapper(string $path, array $expected): void
    {
        self::assertSame($expected, glob($path));
    }
}
