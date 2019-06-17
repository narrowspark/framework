<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests\Cache;

use ArrayObject;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\Testing\Cache\StaticArrayCache;

/**
 * @internal
 */
final class StaticArrayCacheTest extends TestCase
{
    /**
     * @dataProvider provideDataToCache
     *
     * @param mixed $value
     */
    public function testSetContainsFetchDelete($value): void
    {
        $cache = $this->getCacheDriver();
        // Test saving a value, checking if it exists, and fetching it back
        $this->assertTrue($cache->save('key', $value));
        $this->assertTrue($cache->contains('key'));

        if (\is_object($value)) {
            $this->assertEquals($value, $cache->fetch('key'), 'Objects retrieved from the cache must be equal but not necessarily the same reference');
        } else {
            $this->assertSame($value, $cache->fetch('key'), 'Scalar and array data retrieved from the cache must be the same as the original, e.g. same type');
        }

        // Test deleting a value
        $this->assertTrue($cache->delete('key'));
        $this->assertFalse($cache->contains('key'));
        $this->assertFalse($cache->fetch('key'));
    }

    /**
     * @dataProvider provideDataToCache
     *
     * @param mixed $value
     */
    public function testUpdateExistingEntry($value): void
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key', 'old-value'));
        $this->assertTrue($cache->contains('key'));
        $this->assertTrue($cache->save('key', $value));
        $this->assertTrue($cache->contains('key'));

        if (\is_object($value)) {
            $this->assertEquals($value, $cache->fetch('key'), 'Objects retrieved from the cache must be equal but not necessarily the same reference');
        } else {
            $this->assertSame($value, $cache->fetch('key'), 'Scalar and array data retrieved from the cache must be the same as the original, e.g. same type');
        }
    }

    public function testCacheKeyIsCaseSensitive(): void
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key', 'value'));
        $this->assertTrue($cache->contains('key'));
        $this->assertSame('value', $cache->fetch('key'));
        $this->assertFalse($cache->contains('KEY'));
        $this->assertFalse($cache->fetch('KEY'));

        $cache->delete('KEY');

        $this->assertTrue($cache->contains('key'), 'Deleting cache item with different case must not affect other cache item');
    }

    public function testFetchMultiple(): void
    {
        $cache  = $this->getCacheDriver();
        $values = $this->provideDataToCache();
        $saved  = [];

        foreach ($values as $key => $value) {
            $cache->save($key, $value[0]);
            $saved[$key] = $value[0];
        }

        $keys = \array_keys($saved);

        $this->assertEquals(
            $saved,
            $cache->fetchMultiple($keys),
            'Testing fetchMultiple with different data types'
        );
        $this->assertEquals(
            \array_slice($saved, 0, 1),
            $cache->fetchMultiple(\array_slice($keys, 0, 1)),
            'Testing fetchMultiple with a single key'
        );

        $keysWithNonExisting   = [];
        $keysWithNonExisting[] = 'non_existing1';
        $keysWithNonExisting[] = $keys[0];
        $keysWithNonExisting[] = 'non_existing2';
        $keysWithNonExisting[] = $keys[1];
        $keysWithNonExisting[] = 'non_existing3';

        $this->assertEquals(
            \array_slice($saved, 0, 2),
            $cache->fetchMultiple($keysWithNonExisting),
            'Testing fetchMultiple with a subset of keys and mixed with non-existing ones'
        );
    }

    public function testFetchMultipleWithNoKeys(): void
    {
        $cache = $this->getCacheDriver();

        $this->assertSame([], $cache->fetchMultiple([]));
    }

    public function testSaveMultiple(): void
    {
        $cache = $this->getCacheDriver();
        $cache->deleteAll();
        $data = \array_map(function ($value) {
            return $value[0];
        }, $this->provideDataToCache());

        $this->assertTrue($cache->saveMultiple($data));

        $keys = \array_keys($data);

        $this->assertEquals($data, $cache->fetchMultiple($keys));
    }

    public function provideDataToCache(): array
    {
        $obj       = new \stdClass();
        $obj->foo  = 'bar';
        $obj2      = new \stdClass();
        $obj2->bar = 'foo';
        $obj2->obj = $obj;
        $obj->obj2 = $obj2;

        return [
            'array'               => [['one', 2, 3.01]],
            'string'              => ['value'],
            'string_invalid_utf8' => ["\xc3\x28"],
            'string_null_byte'    => ['with' . "\0" . 'null char'],
            'integer'             => [1],
            'float'               => [1.5],
            'object'              => [new ArrayObject(['one', 2, 3.01])],
            'object_recursive'    => [$obj],
            'true'                => [true],
            // the following are considered FALSE in boolean context, but caches should still recognize their existence
            'null'         => [null],
            'false'        => [false],
            'array_empty'  => [[]],
            'string_zero'  => ['0'],
            'integer_zero' => [0],
            'float_zero'   => [0.0],
            'string_empty' => [''],
        ];
    }

    public function testDeleteIsSuccessfulWhenKeyDoesNotExist(): void
    {
        $cache = $this->getCacheDriver();
        $cache->delete('key');

        $this->assertFalse($cache->contains('key'));
        $this->assertTrue($cache->delete('key'));
    }

    public function testDeleteAll(): void
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->deleteAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testDeleteMulti(): void
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 1));
        $this->assertTrue($cache->deleteMultiple(['key1', 'key2', 'key3']));
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
        $this->assertFalse($cache->contains('key3'));
    }

    /**
     * @dataProvider provideCacheIds
     *
     * @param mixed $id
     */
    public function testCanHandleSpecialCacheIds($id): void
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save($id, 'value'));
        $this->assertTrue($cache->contains($id));
        $this->assertEquals('value', $cache->fetch($id));
        $this->assertTrue($cache->delete($id));
        $this->assertFalse($cache->contains($id));
        $this->assertFalse($cache->fetch($id));
    }

    public function testNoCacheIdCollisions(): void
    {
        $cache = $this->getCacheDriver();
        $ids   = $this->provideCacheIds();
        // fill cache with each id having a different value
        foreach ($ids as $index => $id) {
            $cache->save($id[0], $index);
        }

        // then check value of each cache id
        foreach ($ids as $index => $id) {
            $value = $cache->fetch($id[0]);
            $this->assertNotFalse($value, \sprintf('Failed to retrieve data for cache id "%s".', $id[0]));

            if ($index !== $value) {
                $this->fail(\sprintf('Cache id "%s" collides with id "%s".', $id[0], $ids[$value][0]));
            }
        }
    }

    /**
     * Returns cache ids with special characters that should still work.
     *
     * For example, the characters :\/<>"*?| are not valid in Windows filenames. So they must be encoded properly.
     * Each cache id should be considered different from the others.
     */
    public function provideCacheIds(): array
    {
        return [
            [':'],
            ['\\'],
            ['/'],
            ['<'],
            ['>'],
            ['"'],
            ['*'],
            ['?'],
            ['|'],
            ['['],
            [']'],
            ['ä'],
            ['a'],
            ['é'],
            ['e'],
            ['.'], // directory traversal
            ['..'], // directory traversal
            ['-'],
            ['_'],
            ['$'],
            ['%'],
            [' '],
            ["\0"],
            [''],
            [\str_repeat('a', 300)], // long key
            [\str_repeat('a', 113)],
        ];
    }

    public function testLifetime(): void
    {
        $cache = $this->getCacheDriver();
        $cache->save('expire', 'value', 1);

        $this->assertTrue($cache->contains('expire'), 'Data should not be expired yet');

        // @TODO should more TTL-based tests pop up, so then we should mock the `time` API instead
        \sleep(2);

        $this->assertFalse($cache->contains('expire'), 'Data should be expired');
    }

    public function testNoExpire(): void
    {
        $cache = $this->getCacheDriver();
        $cache->save('noexpire', 'value', 0);

        // @TODO should more TTL-based tests pop up, so then we should mock the `time` API instead
        \sleep(1);

        $this->assertTrue($cache->contains('noexpire'), 'Data with lifetime of zero should not expire');
    }

    public function testLongLifetime(): void
    {
        $cache = $this->getCacheDriver();
        $cache->save('longlifetime', 'value', 30 * 24 * 3600 + 1);

        $this->assertTrue($cache->contains('longlifetime'), 'Data with lifetime > 30 days should be accepted');
    }

    public function testFlushAll(): void
    {
        $cache = $this->getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->flushAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testNamespace(): void
    {
        $cache = $this->getCacheDriver();
        $cache->setNamespace('ns1_');

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns2_');

        $this->assertFalse($cache->contains('key1'));
    }

    public function testDeleteAllNamespace(): void
    {
        $cache = $this->getCacheDriver();
        $cache->setNamespace('ns1');

        $this->assertFalse($cache->contains('key1'));

        $cache->save('key1', 'test');

        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns2');

        $this->assertFalse($cache->contains('key1'));

        $cache->save('key1', 'test');

        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns1');

        $this->assertTrue($cache->contains('key1'));

        $cache->deleteAll();

        $this->assertFalse($cache->contains('key1'));

        $cache->setNamespace('ns2');

        $this->assertTrue($cache->contains('key1'));

        $cache->deleteAll();

        $this->assertFalse($cache->contains('key1'));
    }

    public function testSaveReturnsTrueWithAndWithoutTTlSet(): void
    {
        $cache = $this->getCacheDriver();
        $cache->deleteAll();

        $this->assertTrue($cache->save('without_ttl', 'without_ttl'));
        $this->assertTrue($cache->save('with_ttl', 'with_ttl', 3600));
    }

    public function testValueThatIsFalseBooleanIsProperlyRetrieved(): void
    {
        $cache = $this->getCacheDriver();
        $cache->deleteAll();

        $this->assertTrue($cache->save('key1', false));
        $this->assertTrue($cache->contains('key1'));
        $this->assertFalse($cache->fetch('key1'));
    }

    /**
     * @group 147
     * @group 152
     */
    public function testFetchingANonExistingKeyShouldNeverCauseANoticeOrWarning(): void
    {
        $cache        = $this->getCacheDriver();
        $errorHandler = function (): void {
            \restore_error_handler();
            $this->fail('include failure captured');
        };
        \set_error_handler($errorHandler);
        $cache->fetch('key');

        $this->assertSame(
            $errorHandler,
            \set_error_handler(function (): void {
            }),
            'The error handler is the one set by this test, and wasn\'t replaced'
        );

        \restore_error_handler();
        \restore_error_handler();
    }

    public function testGetStats(): void
    {
        $cache = $this->getCacheDriver();
        $cache->fetch('test1');
        $cache->fetch('test2');
        $cache->fetch('test3');
        $cache->save('test1', 123);
        $cache->save('test2', 123);
        $cache->fetch('test1');
        $cache->fetch('test2');
        $cache->fetch('test3');
        $stats = $cache->getStats();

        $this->assertEquals(2, $stats[Cache::STATS_HITS]);
        $this->assertEquals(5, $stats[Cache::STATS_MISSES]); // +1 for internal call to DoctrineNamespaceCacheKey
        $this->assertNotNull($stats[Cache::STATS_UPTIME]);
        $this->assertNull($stats[Cache::STATS_MEMORY_USAGE]);
        $this->assertNull($stats[Cache::STATS_MEMORY_AVAILABLE]);

        $cache->delete('test1');
        $cache->delete('test2');
        $cache->fetch('test1');
        $cache->fetch('test2');
        $cache->fetch('test3');
        $stats = $cache->getStats();

        $this->assertEquals(2, $stats[Cache::STATS_HITS]);
        $this->assertEquals(8, $stats[Cache::STATS_MISSES]); // +1 for internal call to DoctrineNamespaceCacheKey
    }

    private function getCacheDriver(): CacheProvider
    {
        return new StaticArrayCache();
    }
}
