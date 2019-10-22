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

namespace Viserio\Component\Config\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Config\Repository;
use Viserio\Component\Parser\FileLoader;
use Viserio\Contract\Config\Exception\FileNotFoundException;
use Viserio\Contract\Config\ParameterProcessor as ParameterProcessorContract;

/**
 * @internal
 * @covers \Viserio\Component\Config\Repository
 * @small
 */
final class RepositoryTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /** @var \Viserio\Component\Parser\FileLoader */
    private $fileloader;

    /** @var \Viserio\Component\Config\Repository */
    private $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->fileloader = new FileLoader();
        $this->repository = new Repository();
    }

    public function testConstructorInjection(): void
    {
        $values = ['param.test' => 'value'];

        $this->repository->setArray($values);

        self::assertSame($values['param.test'], $this->repository['param.test']);
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

    public function testImportWithAPhpFileThrowsException(): void
    {
        $this->expectException(FileNotFoundException::class);
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
            'foo' => 'bar',
            'func' => static function () {
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
                    'File' => [],
                ],
            ],
        ];

        $expected = [
            'cache' => [
                'default' => 'File',
                'drivers' => [
                    'Memcached' => [],
                    'File' => [],
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
        self::assertArrayNotHasKey('non_existent', (array) $this->repository);
    }

    public function testUnset(): void
    {
        $this->repository['param'] = 'value';

        unset($this->repository['param'], $this->repository['service']);

        self::assertArrayNotHasKey('param', (array) $this->repository);
        self::assertArrayNotHasKey('service', (array) $this->repository);

        $this->repository['foo.bar'] = 'baz';

        $this->repository->offsetUnset('foo.bar');

        self::assertArrayNotHasKey('foo.bar', (array) $this->repository);

        $this->repository->offsetUnset('foo');

        self::assertArrayNotHasKey('foo', (array) $this->repository);
    }

    public function testGetIterator(): void
    {
        self::assertSame(0, $this->repository->getIterator()->count());
    }

    public function testWithProcessor(): void
    {
        \putenv('key=parameter value');

        $this->repository->addParameterProcessor(new EnvParameterProcessor());

        $this->repository->set('key', '%env:key%');

        self::assertSame('parameter value', $this->repository->get('key'));
        self::assertSame('parameter value', $this->repository['key']);

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
                    'root' => 'd',
                ],
                'public' => [
                    'driver' => 'local',
                    'root' => '',
                    'url' => '%env:APP_URL%',
                    'visibility' => [
                        'test' => '%env:key%',
                    ],
                ],
            ],
            'string' => '%env:string%',
        ]);

        self::assertSame(
            [
                'disks' => [
                    'local' => [
                        'driver' => 'local',
                        'root' => 'd',
                    ],
                    'public' => [
                        'driver' => 'local',
                        'root' => '',
                        'url' => 'parameter',
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

        self::assertInstanceOf(ParameterProcessorContract::class, $this->repository->getParameterProcessors()['env']);
    }

    /**
     * @param string       $key
     * @param array|string $parameters
     *
     * @dataProvider provideParametersCases
     */
    public function testParameters(string $key, $parameters): void
    {
        $this->repository->set($key, $parameters);

        self::assertSame($parameters, $this->repository->get($key));
    }

    public function provideParametersCases(): iterable
    {
        return [
            ['baz', 'bar'],
            ['values', [true, false, null, 0, 1000.3, 'true', 'false', 'null']],
            ['binary', "\xf0\xf0\xf0\xf0"],
            ['binary-control-char', "This is a Bell char \x07"],
        ];
    }
}
