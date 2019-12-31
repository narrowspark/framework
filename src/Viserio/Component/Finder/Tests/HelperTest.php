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
    public function provideGlobStreamWrapperCases(): iterable
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
     * @param string   $path
     * @param string[] $expected
     */
    public function testGlobStreamWrapper(string $path, array $expected): void
    {
        self::assertSame($expected, glob($path));
    }
}
