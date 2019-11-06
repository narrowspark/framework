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
use Psr\Http\Message\StreamFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\StreamFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class StreamFactoryAwareTraitTest extends MockeryTestCase
{
    use StreamFactoryAwareTrait;

    public function testSetAndGetStreamFactory(): void
    {
        $this->setStreamFactory(Mockery::mock(StreamFactoryInterface::class));

        self::assertNotNull($this->streamFactory);
    }
}
