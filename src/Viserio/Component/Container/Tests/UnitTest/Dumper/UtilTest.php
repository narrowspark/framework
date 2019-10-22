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

namespace Viserio\Component\Container\Tests\UnitTest\Dumper;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Dumper\Util;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
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
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $expected = \str_replace("\r\n", "\n", $expected);
            $output = \str_replace("\r\n", "\n", $output);
        }

        self::assertEquals($expected, $output);
    }

    /**
     * @dataProvider provideCheckFileCases
     *
     * @param string $file
     * @param string $message
     */
    public function testCheckFile(string $file, string $message): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        Util::checkFile($file);
    }

    public function provideCheckFileCases(): iterable
    {
        return [
            ['', 'Filename was empty.'],
            ['test.file', 'File does not exist.'],
            [__DIR__, 'Is not a file: ' . __DIR__],
        ];
    }
}
