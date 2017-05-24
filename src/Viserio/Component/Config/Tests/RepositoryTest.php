<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Repository;
use Viserio\Component\Parsers\FileLoader;

class RepositoryTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\FileLoader
     */
    private $fileloader;

    public function setUp()
    {
        $this->root       = vfsStream::setup();
        $this->fileloader = new FileLoader();
    }

    public function testConstructorInjection()
    {
        $values = ['param' => 'value'];
        $config = new Repository();

        $config->setArray($values);

        self::assertSame($values['param'], $config['param']);
    }

    public function testGetAndSetLoader()
    {
        $config = new Repository();
        $config->setLoader($this->fileloader);

        self::assertInstanceOf(FileLoader::class, $config->getLoader());
    }

    public function testSetArray()
    {
        $config = new Repository();

        $config->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
        ]);

        self::assertTrue($config->has('123'));
    }

    public function testImport()
    {
        $config = new Repository();
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

        self::assertTrue($config->has('a'));
        self::assertTrue($config->has('b'));
        self::assertTrue($config->has('c'));
    }

    public function testImportWithAPhpFile()
    {
        $config = new Repository();

        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
return [
    "a" => 1,
    "b" => 2,
    "c" => 3,
];
            '
        )->at($this->root);

        $config->import($file->url());

        self::assertTrue($config->has('a'));
        self::assertTrue($config->has('b'));
        self::assertTrue($config->has('c'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage File [test.php] not found.
     */
    public function testImportWithAPhpFileThrowsException()
    {
        $config = new Repository();
        $config->import('test.php');
    }

    public function testGet()
    {
        $config = new Repository();

        $config->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
            'foo'  => 'bar',
            'func' => function () {
                return 'func';
            },
        ]);

        self::assertSame('bar', $config->get('foo'));
        self::assertSame('foo', $config->get('novalue', 'foo'));
        self::assertSame('func', $config->get('func'));
    }

    public function testSet()
    {
        $config = new Repository();

        $config->set('foo', 'bar')
            ->set('bar', 'doo');

        self::assertTrue($config->has('foo'));
        self::assertTrue($config->has('bar'));
    }

    public function testRemove()
    {
        $config = new Repository();

        $config->set('foo', 'bar');

        self::assertTrue($config->has('foo'));

        $config->delete('foo');

        self::assertFalse($config->has('foo'));
    }

    public function testFlattenArray()
    {
        $repository = new Repository();

        $repository->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
        ]);

        self::assertArrayHasKey('123.456.789', $repository->getAllFlat());
    }

    public function testMergeArray()
    {
        // test 1 - keys are string
        $original = [
          'cache' => [
            'default' => 'Memcached',
            'drivers' => [
              'Memcached' => [],
              'File'      => [],
            ],
          ],
        ];

        $expected = [
          'cache' => [
            'default' => 'File',
            'drivers' => [
              'Memcached' => [],
              'File'      => [],
            ],
          ],
        ];

        $repository = new Repository();
        $repository->setArray($original);
        $repository->setArray([
          'cache' => [
            'default' => 'File',
          ],
        ]);

        self::assertEquals($expected, $repository->getAll());

        // test 2 - merge values keyed numeric
        $original = [
            'key1' => [
                'sub1' => 1,
                'sub2' => [
                    'E1',
                    'E2',
                    'E3',
                ],
            ],
        ];

        $expected = [
            'key1' => [
                'sub1' => 2,
                'sub2' => [
                    'E1',
                    'E2',
                    'E3',
                    'E5',
                    'E6',
                ],
            ],
        ];

        $repository = new Repository();
        $repository->setArray($original);
        $repository->setArray([
            'key1' => [
                'sub1' => 2,
                'sub2' => [
                    'E5',
                    'E6',
                ],
            ],
        ]);

        self::assertEquals($expected, $repository->getAll());
    }

    public function testSetAndGet()
    {
        $repository = new Repository();

        $repository['foo'] = 'bar';

        self::assertEquals($repository['foo'], 'bar');
    }

    public function testGetKeys()
    {
        $repository = new Repository();

        $repository->setArray([
            'foo' => 'bar',
            'bar' => [
                'baz' => 'narrowspark',
            ],
        ]);
        $keys = $repository->getKeys();

        self::assertEquals($keys[0], 'foo');
        self::assertEquals($keys[1], 'bar.baz');
    }

    public function testWithNamespacedKey()
    {
        $repository = new Repository();

        $repository['my.namespaced.keyname'] = 'My Value';

        self::assertArrayHasKey('my', $repository);
        self::assertArrayHasKey('namespaced', $repository['my']);
        self::assertArrayHasKey('keyname', $repository['my.namespaced']);

        self::assertEquals('My Value', $repository['my.namespaced.keyname']);
    }

    public function testWithString()
    {
        $repository = new Repository();

        $repository['keyname'] = 'My Value';

        self::assertEquals('My Value', $repository['keyname']);
    }

    public function testIsset()
    {
        $repository = new Repository();

        $repository['param'] = 'value';

        self::assertTrue(isset($repository['param']));
        self::assertFalse(isset($repository['non_existent']));
    }

    public function testUnset()
    {
        $repository = new Repository();

        $repository['param'] = 'value';

        unset($repository['param'], $repository['service']);

        self::assertFalse(isset($repository['param']));
        self::assertFalse(isset($repository['service']));

        $repository['foo.bar'] = 'baz';

        $repository->offsetUnset('foo.bar');

        self::assertFalse(isset($repository['foo.bar']));

        $repository->offsetUnset('foo');

        self::assertFalse(isset($repository['foo']));
    }

    public function testGetIterator()
    {
        $repository = new Repository();

        self::assertInstanceOf('ArrayIterator', $repository->getIterator());
    }
}
