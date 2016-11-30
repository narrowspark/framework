<?php
declare(strict_types=1);
namespace Viserio\Config\Tests;

use Viserio\Config\Repository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
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

        $repository = new Repository();
        $repository->setArray($original);
        $repository->setArray([
          'cache' => [
            'default' => 'File',
          ],
        ]);

        self::assertEquals($expected, $repository->getAllNested());

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

        self::assertEquals($expected, $repository->getAllNested());
    }

    public function testSetArray()
    {
        $repository = new Repository();

        $repository->setArray([
            'foo' => 'bar',
        ]);

        self::assertEquals($repository['foo'], 'bar');
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
        $this->arrayHasKey($repository, 'my');
        $this->arrayHasKey($repository['my'], 'namespaced');
        $this->arrayHasKey($repository['my.namespaced'], 'keyname');
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
