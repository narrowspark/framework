<?php
namespace Viserio\Config\Test;

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

        $this->assertArrayHasKey('123.456.789', $repository->getAllFlat());
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

        $this->assertEquals($expected, $repository->getAllNested());

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

        $this->assertEquals($expected, $repository->getAllNested());
    }

    public function testSetArray()
    {
        $repository = new Repository();

        $repository->setArray([
            'foo' => 'bar',
        ]);

        $this->assertEquals($repository['foo'], 'bar');
    }

    public function testSetAndGetSeparator()
    {
        $repository = new Repository();

        $repository->setSeparator('-');
        $this->assertEquals($repository->getSeparator(), '-');
    }

    public function testSetAndGet()
    {
        $repository = new Repository();

        $repository['foo'] = 'bar';

        $this->assertEquals($repository['foo'], 'bar');
    }

    public function testKeys()
    {
        $repository = new Repository();

        $repository->setArray([
            'foo' => 'bar',
        ]);
        $keys = $repository->getKeys();

        $this->assertEquals($keys[0], 'foo');
    }

    public function testWithNamespacedKey()
    {
        $repository = new Repository();

        $repository['my.namespaced.keyname'] = 'My Value';
        $this->arrayHasKey($repository, 'my');
        $this->arrayHasKey($repository['my'], 'namespaced');
        $this->arrayHasKey($repository['my.namespaced'], 'keyname');
        $this->assertEquals('My Value', $repository['my.namespaced.keyname']);
    }

    public function testWithString()
    {
        $repository = new Repository();

        $repository['keyname'] = 'My Value';

        $this->assertEquals('My Value', $repository['keyname']);
    }

    public function testIsset()
    {
        $repository = new Repository();

        $repository['param'] = 'value';
        $this->assertTrue(isset($repository['param']));
        $this->assertFalse(isset($repository['non_existent']));
    }

    public function testUnset()
    {
        $repository = new Repository();

        $repository['param'] = 'value';
        unset($repository['param'], $repository['service']);
        $this->assertFalse(isset($repository['param']));
        $this->assertFalse(isset($repository['service']));
    }
}
