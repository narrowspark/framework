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

namespace Viserio\Contract\HttpFactory\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\ServerRequestFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ServerRequestFactoryAwareTraitTest extends MockeryTestCase
{
    use ServerRequestFactoryAwareTrait;

    public function testSetAndGetServerRequestFactory(): void
    {
        $this->setServerRequestFactory(Mockery::mock(ServerRequestFactoryInterface::class));

        self::assertNotNull($this->serverRequestFactory);
    }
}
