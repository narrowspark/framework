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
use Psr\Http\Message\StreamFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\StreamFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
