<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Config\Repository;
use Viserio\Component\Contract\Config\ParameterProcessor as ParameterProcessorContract;
use Viserio\Component\Parser\FileLoader;

/**
 * @internal
 */
final class RepositoryTest extends TestCase
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
    protected function setUp(): void
    {
        $this->root       = vfsStream::setup();
        $this->fileloader = new FileLoader();
        $this->repository = new Repository();
    }

    public function testConstructorInjection(): void
    {
        $values = ['param.test' => 'value'];

        $this->repository->setArray($values, true);

        static::assertSame($values['param.test'], $this->repository['param.test']);
    }

    public function testGetAndSetLoader(): void
    {
        $this->repository->setLoader($this->fileloader);

        static::assertInstanceOf(FileLoader::class, $this->repository->getLoader());
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

        static::assertTrue($this->repository->has('123'));
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

        static::assertTrue($this->repository->has('a'));
        static::assertTrue($this->repository->has('b'));
        static::assertTrue($this->repository->has('c'));
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

        static::assertTrue($this->repository->has('a'));
        static::assertTrue($this->repository->has('b'));
        static::assertTrue($this->repository->has('c'));
    }

    public function testImportWithAPhpFileThrowsException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Config\Exception\FileNotFoundException::class);
        $this->expectExceptionMessage('File [test.php] not found.');

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

        static::assertSame('bar', $this->repository->get('foo'));
        static::assertSame('foo', $this->repository->get('novalue', 'foo'));
        static::assertSame('func', $this->repository->get('func'));
    }

    public function testSet(): void
    {
        $this->repository->set('foo', 'bar')
            ->set('bar', 'doo');

        static::assertTrue($this->repository->has('foo'));
        static::assertTrue($this->repository->has('bar'));
    }

    public function testRemove(): void
    {
        $this->repository->set('foo', 'bar');

        static::assertTrue($this->repository->has('foo'));

        $this->repository->delete('foo');

        static::assertFalse($this->repository->has('foo'));
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

        static::assertArrayHasKey('123.456.789', $this->repository->getAllFlat());
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

        static::assertEquals($expected, $this->repository->getAll());

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

        static::assertEquals($expected, $repository->getAll());
    }

    public function testSetAndGet(): void
    {
        $this->repository['foo'] = 'bar';

        static::assertEquals($this->repository['foo'], 'bar');
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

        static::assertEquals($keys[0], 'foo');
        static::assertEquals($keys[1], 'bar.baz');
    }

    public function testWithNamespacedKey(): void
    {
        $this->repository['my.namespaced.keyname'] = 'My Value';

        static::assertArrayHasKey('my', $this->repository);
        static::assertArrayHasKey('namespaced', $this->repository['my']);
        static::assertArrayHasKey('keyname', $this->repository['my.namespaced']);

        static::assertEquals('My Value', $this->repository['my.namespaced.keyname']);
    }

    public function testWithString(): void
    {
        $this->repository['keyname'] = 'My Value';

        static::assertEquals('My Value', $this->repository['keyname']);
    }

    public function testIsset(): void
    {
        $this->repository['param'] = 'value';

        static::assertTrue(isset($this->repository['param']));
        static::assertArrayNotHasKey('non_existent', (array) $this->repository);
    }

    public function testUnset(): void
    {
        $this->repository['param'] = 'value';

        unset($this->repository['param'], $this->repository['service']);

        static::assertArrayNotHasKey('param', (array) $this->repository);
        static::assertArrayNotHasKey('service', (array) $this->repository);

        $this->repository['foo.bar'] = 'baz';

        $this->repository->offsetUnset('foo.bar');

        static::assertArrayNotHasKey('foo.bar', (array) $this->repository);

        $this->repository->offsetUnset('foo');

        static::assertArrayNotHasKey('foo', (array) $this->repository);
    }

    public function testGetIterator(): void
    {
        static::assertInstanceOf('ArrayIterator', $this->repository->getIterator());
    }

    public function testWithProcessor(): void
    {
        \putenv('key=parameter value');

        $this->repository->addParameterProcessor(new EnvParameterProcessor());

        $this->repository->set('key', '%env:key%');

        static::assertSame('parameter value', $this->repository->get('key'));
        static::assertSame('parameter value', $this->repository['key']);

        \putenv('key=');
        \putenv('key');
    }

    public function testGetAllProcessed(): void
    {
        \putenv('key=parameter value');
        \putenv('APP_URL=parameter');
        \putenv('string=string para');

        $this->repository->addParameterProcessor(new EnvParameterProcessor());

        $this->repository->setArray([
            'disks' => [
                'local' => [
                    'driver' => 'local',
                    'root'   => 'd',
                ],
                'public' => [
                    'driver'     => 'local',
                    'root'       => '',
                    'url'        => '%env:APP_URL%',
                    'visibility' => [
                        'test' => '%env:key%',
                    ],
                ],
            ],
            'string' => '%env:string%',
        ]);

        static::assertSame(
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
        $processor = new EnvParameterProcessor();

        $this->repository->addParameterProcessor($processor);

        static::assertInstanceOf(ParameterProcessorContract::class, $this->repository->getParameterProcessors()['env']);
    }
}
