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

namespace Viserio\Component\Container\Tests\Unit\PhpParser;

use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\PhpParser\PrettyPrinter;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PhpParser\PrettyPrinter
 *
 * @small
 */
final class PrettyPrinterTest extends TestCase
{
    /** @var PrettyPrinter */
    private $printer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->printer = new PrettyPrinter();
    }

    /**
     * @dataProvider provideDoubleSlashEscapingCases()
     */
    public function testDoubleSlashEscaping(string $content, string $expectedOutput): void
    {
        $printed = $this->printer->prettyPrint(new String_($content));

        self::assertSame($expectedOutput, $printed);
    }

    public static function provideDoubleSlashEscapingCases(): iterable
    {
        yield ['Vendor\Name', "'Vendor\\Name'"];

        yield ['Vendor\\', "'Vendor\\\\'"];

        yield ['Vendor\'Name', "'Vendor\\'Name'"];
    }

    public function testYield(): void
    {
        $printed = $this->printer->prettyPrint(new Yield_(new String_('value')));

        self::assertSame("yield 'value'", $printed);

        $printed = $this->printer->prettyPrint(new Yield_());

        self::assertSame('yield', $printed);
    }
}
