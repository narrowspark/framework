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
use Psr\Http\Message\UploadedFileFactoryInterface;
use Viserio\Contract\HttpFactory\Traits\UploadedFileFactoryAwareTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class UploadedFileFactoryAwareTraitTest extends MockeryTestCase
{
    use UploadedFileFactoryAwareTrait;

    public function testSetAndGetUploadedFileFactory(): void
    {
        $this->setUploadedFileFactory(Mockery::mock(UploadedFileFactoryInterface::class));

        self::assertNotNull($this->uploadedFileFactory);
    }
}
