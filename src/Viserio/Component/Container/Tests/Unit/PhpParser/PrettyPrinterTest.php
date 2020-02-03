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
     * @param string $content
     * @param string $expectedOutput
     *
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
