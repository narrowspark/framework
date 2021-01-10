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

namespace Viserio\Component\Container\Tests\Unit\Dumper;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Dumper\Util;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Dumper\Util
 *
 * @small
 */
final class UtilTest extends TestCase
{
    public function testStripComments(): void
    {
        $source = <<<'EOF'
<?php

$string = 'string should not be   modified';

$string = 'string should not be

modified';


$heredoc = <<<HD


Heredoc should not be   modified {$a[1+$b]}


HD;

$nowdoc = <<<'ND'


Nowdoc should not be   modified


ND;

/**
 * some class comments to strip
 */
class TestClass
{
    /**
     * some method comments to strip
     */
    public function doStuff()
    {
        // inline comment
    }
}
EOF;
        $expected = <<<'EOF'
<?php
$string = 'string should not be   modified';
$string = 'string should not be

modified';
$heredoc = <<<HD


Heredoc should not be   modified {$a[1+$b]}


HD;
$nowdoc = <<<'ND'


Nowdoc should not be   modified


ND;
class TestClass
{
    public function doStuff()
    {
        }
}
EOF;
        $output = Util::stripComments($source);

        // Heredocs are preserved, making the output mixing Unix and Windows line
        // endings, switching to "\n" everywhere on Windows to avoid failure.
        if (\PHP_OS_FAMILY === 'Windows') {
            $expected = \str_replace("\r\n", "\n", $expected);
            $output = \str_replace("\r\n", "\n", $output);
        }

        self::assertEquals($expected, $output);
    }

    /**
     * @dataProvider provideCheckFileCases
     */
    public function testCheckFile(string $file, string $message): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        Util::checkFile($file);
    }

    public static function provideCheckFileCases(): iterable
    {
        return [
            ['', 'Filename was empty.'],
            ['test.file', 'File does not exist.'],
            [__DIR__, 'Is not a file: ' . __DIR__],
        ];
    }
}
