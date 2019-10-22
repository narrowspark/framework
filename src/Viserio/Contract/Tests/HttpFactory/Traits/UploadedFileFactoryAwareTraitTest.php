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
use Psr\Http\Message\UploadedFileFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\UploadedFileFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class UploadedFileFactoryAwareTraitTest extends MockeryTestCase
{
    use UploadedFileFactoryAwareTrait;

    public function testSetAndGetUploadedFileFactory(): void
    {
        $this->setUploadedFileFactory(\Mockery::mock(UploadedFileFactoryInterface::class));

        self::assertNotNull($this->uploadedFileFactory);
    }
}
