<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Traits;

use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Throwable;
use Viserio\Component\Support\Traits\ArrayPrettyPrintTrait;

class ArrayPrettyPrintTraitTest extends TestCase
{
    use ArrayPrettyPrintTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    public function testWithSimpleArray()
    {
        $array = $this->getPrettyPrintArray([1 => 'foo', '188.29614911019327165' => 'bar', 'foo' => '889614911019327165', 'fooa' => 18896141256]);
        $file  = vfsStream::newFile('simpleArray.php')
            ->withContent('[
    1 => \'foo\',
    \'188.29614911019327165\' => \'bar\',
    \'foo\' => 889614911019327165,
    \'fooa\' => 18896141256,
]')
            ->at($this->root);
        $outFile  = vfsStream::newFile('simpleArrayOutput.php')
            ->withContent($array)
            ->at($this->root);

        self::assertSame(file_get_contents($file->url()), file_get_contents($outFile->url()));
    }

    public function testArrayWithClassAndInterface()
    {
        $array = $this->getPrettyPrintArray([1 => Exception::class, Throwable::class => 'error', 'foo' => 'bar', 'fooa' => 1.2]);
        $file  = vfsStream::newFile('classAndInterfaceArray.php')
            ->withContent('[
    1 => \\Exception::class,
    \\Throwable::class => \'error\',
    \'foo\' => \'bar\',
    \'fooa\' => 1.2,
]')
            ->at($this->root);
        $outFile  = vfsStream::newFile('classAndInterfaceArrayOutput.php')
            ->withContent($array)
            ->at($this->root);

        self::assertSame(file_get_contents($file->url()), file_get_contents($outFile->url()));
    }

    public function testWithDimensionalArray()
    {
        $array = $this->getPrettyPrintArray([1 => ['foo'], 'bar' => 2]);
        $file  = vfsStream::newFile('dimensionalArray.php')
            ->withContent('[
    1 => [
        0 => \'foo\',
    ],
    \'bar\' => 2,
]')
            ->at($this->root);
        $outFile  = vfsStream::newFile('dimensionalArrayOutput.php')
            ->withContent($array)
            ->at($this->root);

        self::assertSame(file_get_contents($file->url()), file_get_contents($outFile->url()));
    }
}
