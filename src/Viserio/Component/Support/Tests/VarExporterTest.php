<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Support\Tests\Fixture\Controller;
use Viserio\Component\Support\VarExporter;

class VarExporterTest extends TestCase
{
    public function exportCases()
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
           [[], '[]'],
           [[1], '[0 => 1]'],
           [[1, 2, 3], '[0 => 1,1 => 2,2 => 3,]'],
           [[1, '2', 3], '[0 => 1,1 => \'2\',2 => 3,]'],
           [['foo' => 1, [2, 3]], '[\'foo\' => 1,0 => [0 => 2,1 => 3,],]'],
           [new stdClass(), '(object)[]'],
           [(object) ['foo' => 'bar'], '(object)[\'foo\' => \'bar\']'],
           [new Controller(), 'unserialize(' . var_export(serialize(new Controller()), true) . ')'],
       ];
    }

    /**
     * @dataProvider exportCases
     *
     * @param mixed $value
     * @param mixed $code
     */
    public function testConvertsValueToValidPhp($value, $code)
    {
        $exported  = VarExporter::export($value);
        $evaluated = eval('return ' . $exported . ';');

        self::assertSame($code, $exported, '');
        self::assertEquals($value, $evaluated);
    }
}
