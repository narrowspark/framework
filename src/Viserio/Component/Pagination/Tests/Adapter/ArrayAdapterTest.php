<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Pagination\Adapter\ArrayAdapter;

/**
 * @internal
 */
final class ArrayAdapterTest extends TestCase
{
    public function testGetItems(): void
    {
        $adapter = new ArrayAdapter(['test'], 1);

        static::assertSame(['test'], $adapter->getItems());
    }

    public function testGetItemsPerPage(): void
    {
        $adapter = new ArrayAdapter(['test'], 1);

        static::assertSame(1, $adapter->getItemsPerPage());
    }
}
