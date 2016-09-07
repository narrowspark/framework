<?php
declare(strict_types=1);
namespace Viserio\Config\Tests;

use org\bovigo\vfs\vfsStream;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;
use Viserio\Parsers\FileLoader;
use Viserio\Parsers\TaggableParser;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\FileLoader
     */
    private $fileloader;

    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->fileloader = new FileLoader(new TaggableParser(), []);
    }

    public function testConstructorInjection()
    {
        $values = ['param' => 'value'];
        $config = new ConfigManager(new Repository());

        $config->setArray($values);

        $this->assertSame($values['param'], $config['param']);
    }

    public function testGetAndSetLoader()
    {
        $config = new ConfigManager(new Repository());
        $config->setLoader($this->fileloader);

        $this->assertInstanceOf(FileLoader::class, $config->getLoader());
    }

    public function testSetArray()
    {
        $config = new ConfigManager(new Repository());

        $config->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
        ]);

        $this->assertTrue($config->has('123'));
    }

    public function testImport()
    {
        $config = new ConfigManager(new Repository());
        $config->setLoader($this->fileloader);

        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "b":2,
    "c":3
}
            '
        )->at($this->root);

        $config->import($file->url());

        $this->assertTrue($config->has('a'));
        $this->assertTrue($config->has('b'));
        $this->assertTrue($config->has('c'));
    }

    public function testImportWithGroup()
    {
        $config = new ConfigManager(new Repository());
        $config->setLoader($this->fileloader);

        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "b":2,
    "c":3
}
            '
        )->at($this->root);

        $config->import($file->url(), 'test');

        $this->assertTrue($config->has('test::a'));
        $this->assertSame(2, $config->get('test::b'));
        $this->assertTrue($config->has('test::c'));
    }

    public function testGet()
    {
        $config = new ConfigManager(new Repository());

        $config->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
            'foo' => 'bar',
            'func' => function () {
                return 'func';
            },
        ]);

        $this->assertSame('bar', $config->get('foo'));
        $this->assertSame('foo', $config->get('novalue', 'foo'));
        $this->assertSame('func', $config->get('func'));
    }

    public function testSet()
    {
        $config = new ConfigManager(new Repository());

        $config->set('foo', 'bar')
            ->set('bar', 'doo');

        $this->assertTrue($config->has('foo'));
        $this->assertTrue($config->has('bar'));
    }

    public function testRemove()
    {
        $config = new ConfigManager(new Repository());

        $config->set('foo', 'bar');

        $this->assertTrue($config->has('foo'));

        $config->delete('foo');

        $this->assertFalse($config->has('foo'));
    }

    public function testGetIterator()
    {
        $config = new ConfigManager(new Repository());

        $this->assertInstanceOf('ArrayIterator', $config->getIterator());
    }

    public function testCall()
    {
        $config = new ConfigManager(new Repository());

        $config->set('foo', 'bar');

        $this->assertSame(1, count($config->getKeys()));
        $config->setLoader($this->fileloader);

        $this->assertInstanceOf(TaggableParser::class, $config->getParser());
    }
}
