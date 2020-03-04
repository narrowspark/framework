<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Pagination\Tests\Presenters;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Component\Pagination\Adapter\ArrayAdapter;
use Viserio\Component\Pagination\Paginator;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class Bootstrap4Test extends MockeryTestCase
{
    public function testPaginatorRenderBootstrap(): void
    {
        $array = new ArrayAdapter(['item3', 'item4', 'item5'], 2);

        $request = Mockery::mock(ServerRequestInterface::class);

        $request->shouldReceive('getQueryParams')
            ->times(6)
            ->andReturn(['page' => '2']);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn((new UriFactory())->createUri('http://example.com/'));

        $pagi = new Paginator($array, $request);

        self::assertSame(
            '<ul class="pagination"><li class="page-item"><a class="page-link" href="/?page=1" rel="prev">&laquo;</a></li><li class="page-item"><a class="page-link" href="/?page=3" rel="next">&raquo;</a></li></ul>',
            $pagi->render('bootstrap4')
        );
    }
}
