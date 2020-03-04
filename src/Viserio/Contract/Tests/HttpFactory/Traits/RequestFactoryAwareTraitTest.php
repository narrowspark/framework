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
use Psr\Http\Message\RequestFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\RequestFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
