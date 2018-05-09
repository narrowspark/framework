<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Repository;
use Viserio\Component\Config\Tests\Fixture\FixtureParameterProcessor;
use Viserio\Component\Contract\Config\ParameterProcessor as ParameterProcessorContract;
use Viserio\Component\Parser\FileLoader;

class RepositoryTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parser\FileLoader
     */
    private $fileloader;

    /**
     * @var \Viserio\Component\Config\Repository
     */
    private $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->root       = vfsStream::setup();
        $this->fileloader = new FileLoader();
        $this->repository = new Repository();
    }

    public function testConstructorInjection(): void
    {
        $values = ['param' => 'value'];

        $this->repository->setArray($values);

        self::assertSame($values['param'], $this->repository['param']);
    }

    public function testGetAndSetLoader(): void
    {
        $this->repository->setLoader($this->fileloader);

        self::assertInstanceOf(FileLoader::class, $this->repository->getLoader());
    }

    public function testSetArray(): void
    {
        $this->repository->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
        ]);

        self::assertTrue($this->repository->has('123'));
    }

    public function testImport(): void
    {
        $this->repository->setLoader($this->fileloader);

        $file = vfsStream::newFile('temp.json')->withContent(
            '
{
    "a":1,
    "b":2,
    "c":3
}
            '
        )->at($this->root);

        $this->repository->import($file->url());

        self::assertTrue($this->repository->has('a'));
        self::assertTrue($this->repository->has('b'));
        self::assertTrue($this->repository->has('c'));
    }

    public function testImportWithAPhpFile(): void
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
return [
    "a" => 1,
    "b" => 2,
    "c" => 3,
];
            '
        )->at($this->root);

        $this->repository->import($file->url());

        self::assertTrue($this->repository->has('a'));
        self::assertTrue($this->repository->has('b'));
        self::assertTrue($this->repository->has('c'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Config\Exception\FileNotFoundException
     * @expectedExceptionMessage File [test.php] not found.
     */
    public function testImportWithAPhpFileThrowsException(): void
    {
        $this->repository->import('test.php');
    }

    public function testGet(): void
    {
        $this->repository->setArray([
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

        self::assertSame('bar', $this->repository->get('foo'));
        self::assertSame('foo', $this->repository->get('novalue', 'foo'));
        self::assertSame('func', $this->repository->get('func'));
    }

    public function testSet(): void
    {
        $this->repository->set('foo', 'bar')
            ->set('bar', 'doo');

        self::assertTrue($this->repository->has('foo'));
        self::assertTrue($this->repository->has('bar'));
    }

    public function testRemove(): void
    {
        $this->repository->set('foo', 'bar');

        self::assertTrue($this->repository->has('foo'));

        $this->repository->delete('foo');

        self::assertFalse($this->repository->has('foo'));
    }

    public function testFlattenArray(): void
    {
        $this->repository->setArray([
            '123' => [
                '456' => [
                    '789' => 1,
                ],
            ],
        ]);

        self::assertArrayHasKey('123.456.789', $this->repository->getAllFlat());
    }

    public function testMergeArray(): void
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

        $this->repository->setArray($original);
        $this->repository->setArray([
          'cache' => [
            'default' => 'File',
          ],
        ]);

        self::assertEquals($expected, $this->repository->getAll());

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

    public function testSetAndGet(): void
    {
        $this->repository['foo'] = 'bar';

        self::assertEquals($this->repository['foo'], 'bar');
    }

    public function testGetKeys(): void
    {
        $this->repository->setArray([
            'foo' => 'bar',
            'bar' => [
                'baz' => 'narrowspark',
            ],
        ]);
        $keys = $this->repository->getKeys();

        self::assertEquals($keys[0], 'foo');
        self::assertEquals($keys[1], 'bar.baz');
    }

    public function testWithNamespacedKey(): void
    {
        $this->repository['my.namespaced.keyname'] = 'My Value';

        self::assertArrayHasKey('my', $this->repository);
        self::assertArrayHasKey('namespaced', $this->repository['my']);
        self::assertArrayHasKey('keyname', $this->repository['my.namespaced']);

        self::assertEquals('My Value', $this->repository['my.namespaced.keyname']);
    }

    public function testWithString(): void
    {
        $this->repository['keyname'] = 'My Value';

        self::assertEquals('My Value', $this->repository['keyname']);
    }

    public function testIsset(): void
    {
        $this->repository['param'] = 'value';

        self::assertTrue(isset($this->repository['param']));
        self::assertFalse(isset($this->repository['non_existent']));
    }

    public function testUnset(): void
    {
        $this->repository['param'] = 'value';

        unset($this->repository['param'], $this->repository['service']);

        self::assertFalse(isset($this->repository['param']));
        self::assertFalse(isset($this->repository['service']));

        $this->repository['foo.bar'] = 'baz';

        $this->repository->offsetUnset('foo.bar');

        self::assertFalse(isset($this->repository['foo.bar']));

        $this->repository->offsetUnset('foo');

        self::assertFalse(isset($this->repository['foo']));
    }

    public function testGetIterator(): void
    {
        self::assertInstanceOf('ArrayIterator', $this->repository->getIterator());
    }

    public function testWithProcessor(): void
    {
        \putenv('key=parameter value');

        $this->repository->addParameterProcessor(new FixtureParameterProcessor());

        $this->repository->set('key', 'fixture(key)');

        self::assertSame('parameter value', $this->repository->get('key'));

        \putenv('key=');
        \putenv('key');
    }

    public function testGetAllProcessed(): void
    {
        \putenv('key=parameter value');
        \putenv('APP_URL=parameter');
        \putenv('string=string para');

        $this->repository->addParameterProcessor(new FixtureParameterProcessor());

        $this->repository->setArray([
            'disks' => [
                'local' => [
                    'driver' => 'local',
                    'root'   => 'd',
                ],
                'public' => [
                    'driver'     => 'local',
                    'root'       => '',
                    'url'        => 'fixture(APP_URL)',
                    'visibility' => [
                        'test' => 'fixture(key)',
                    ],
                ],
            ],
            'string' => 'fixture(string)',
        ]);

        self::assertSame(
            [
                'disks' => [
                    'local' => [
                        'driver' => 'local',
                        'root'   => 'd',
                    ],
                    'public' => [
                        'driver'     => 'local',
                        'root'       => '',
                        'url'        => 'parameter',
                        'visibility' => [
                            'test' => 'parameter value',
                        ],
                    ],
                ],
                'string' => 'string para',
            ],
            $this->repository->getAllProcessed()
        );

        \putenv('key=');
        \putenv('key');
        \putenv('APP_URL=');
        \putenv('APP_URL');
        \putenv('string=');
        \putenv('string');
    }

    public function testGetParameterProcessors(): void
    {
        $processor = new FixtureParameterProcessor();

        $this->repository->addParameterProcessor($processor);

        self::assertInstanceOf(ParameterProcessorContract::class, $this->repository->getParameterProcessors()['fixture']);
    }
}
