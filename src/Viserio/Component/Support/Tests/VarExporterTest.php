<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Support\Tests\Fixture\Controller;
use Viserio\Component\Support\VarExporter;

/**
 * @internal
 */
final class VarExporterTest extends TestCase
{
    public function exportCaseProvider(): array
    {
        return [
            [1, '1'],
            [-1, '-1'],
            [34243, '34243'],
            [1.0, '1.0'],
            [-1.954, '-1.954'],
            [true, 'true'],
            [false, 'false'],
            [null, 'null'],
            ['abcdef', '\'abcdef\''],
            ['', '\'\''],
            [[], '[' . \PHP_EOL . \PHP_EOL . ']'],
            [[1], '[' . \PHP_EOL . '    0 => 1,' . \PHP_EOL . ']'],
            [[1, 2, 3], '[' . \PHP_EOL . '    0 => 1,' . \PHP_EOL . '    1 => 2,' . \PHP_EOL . '    2 => 3,' . \PHP_EOL . ']'],
            [[1, '2', 3], '[' . \PHP_EOL . '    0 => 1,' . \PHP_EOL . '    1 => \'2\',' . \PHP_EOL . '    2 => 3,' . \PHP_EOL . ']'],
            [['foo' => 1, [2, 3]], '[' . \PHP_EOL . '    \'foo\' => 1,' . \PHP_EOL . '    0 => [' . \PHP_EOL . '        0 => 2,' . \PHP_EOL . '        1 => 3,' . \PHP_EOL . '    ],' . \PHP_EOL . ']'],
            [new stdClass(), '(object)[' . \PHP_EOL . \PHP_EOL . ']'],
            [(object) ['foo' => 'bar'], '(object)[' . \PHP_EOL . '    \'foo\' => \'bar\',' . \PHP_EOL . ']'],
            [new Controller(), 'unserialize(' . \var_export(\serialize(new Controller()), true) . ')'],
        ];
    }

    /**
     * @dataProvider exportCaseProvider
     *
     * @param mixed $value
     * @param mixed $code
     */
    public function testConvertsValueToValidPhp($value, $code): void
    {
        $exported  = VarExporter::export($value);
        $evaluated = eval('return ' . $exported . ';');

        $this->assertSame($code, $exported, '');
        $this->assertEquals($value, $evaluated);
    }
}
