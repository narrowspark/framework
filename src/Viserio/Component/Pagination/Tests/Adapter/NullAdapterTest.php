<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Pagination\Adapter\NullAdapter;

/**
 * @internal
 */
final class NullAdapterTest extends TestCase
{
    public function testGetItems(): void
    {
        $adapter = new NullAdapter();

        $this->assertSame([], $adapter->getItems());
    }

    public function testGetItemsPerPage(): void
    {
        $adapter = new NullAdapter();

        $this->assertSame(0, $adapter->getItemsPerPage());
    }
}
