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
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\UriFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class UriFactoryAwareTraitTest extends MockeryTestCase
{
    use UriFactoryAwareTrait;

    public function testSetAndGetUriFactory(): void
    {
        $this->setUriFactory(\Mockery::mock(UriFactoryInterface::class));

        self::assertNotNull($this->uriFactory);
    }
}
