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
 */
final class Foundation6Test extends MockeryTestCase
{
    public function testPaginatorRenderFoundation6(): void
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
            '<ul class="pagination" role="navigation"><li class="pagination-previous"><a href="/?page=1" rel="prev">&laquo;</a></li><li class="pagination-next"><a href="/?page=3" rel="next">&raquo;</a></li></ul>',
            $pagi->render('foundation6')
        );
    }
}
