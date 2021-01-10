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
use Psr\Http\Message\ResponseFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ResponseFactoryAwareTraitTest extends MockeryTestCase
{
    use ResponseFactoryAwareTrait;

    public function testSetAndGetResponseFactory(): void
    {
        $this->setResponseFactory(Mockery::mock(ResponseFactoryInterface::class));

        self::assertNotNull($this->responseFactory);
    }
}
