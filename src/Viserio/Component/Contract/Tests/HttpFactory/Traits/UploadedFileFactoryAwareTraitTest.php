<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Interop\Http\Factory\UploadedFileFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\HttpFactory\Traits\UploadedFileFactoryAwareTrait;

/**
 * @internal
 */
final class UploadedFileFactoryAwareTraitTest extends MockeryTestCase
{
    use UploadedFileFactoryAwareTrait;

    public function testSetAndGetUploadedFileFactory(): void
    {
        $this->setUploadedFileFactory($this->mock(UploadedFileFactoryInterface::class));

        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $this->uploadedFileFactory);
    }
}
