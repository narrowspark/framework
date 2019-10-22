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

namespace Viserio\Contract\HttpFactory\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\ServerRequestFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class ServerRequestFactoryAwareTraitTest extends MockeryTestCase
{
    use ServerRequestFactoryAwareTrait;

    public function testSetAndGetServerRequestFactory(): void
    {
        $this->setServerRequestFactory(\Mockery::mock(ServerRequestFactoryInterface::class));

        self::assertNotNull($this->serverRequestFactory);
    }
}
