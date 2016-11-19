<?php
declare(strict_types=1);
namespace Viserio\Pagination\Tests;

use Viserio\Pagination\Paginator;

class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testPaginatorGeneratesUrlsWithoutTrailingSlash()
    {
        $pagi = new Paginator(['item1', 'item2', 'item3'], 2, 2, ['path' => 'http://example.com/test']);

        $this->assertEquals('http://example.com/test?page=1', $pagi->previousPageUrl());
    }
}
