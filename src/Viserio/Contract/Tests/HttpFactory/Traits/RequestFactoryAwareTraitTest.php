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

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\RequestFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class RequestFactoryAwareTraitTest extends MockeryTestCase
{
    use RequestFactoryAwareTrait;

    public function testSetAndGetRequestFactory(): void
    {
        $this->setRequestFactory(Mockery::mock(RequestFactoryInterface::class));

        self::assertNotNull($this->requestFactory);
    }
}
