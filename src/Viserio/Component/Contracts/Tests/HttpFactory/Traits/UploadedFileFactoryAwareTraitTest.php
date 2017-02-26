<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\UploadedFileFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\UploadedFileFactoryAwareTrait;

class UploadedFileFactoryAwareTraitTest extends MockeryTestCase
{
    use UploadedFileFactoryAwareTrait;

    public function testSetAndGetUploadedFileFactory()
    {
        $this->setUploadedFileFactory($this->mock(UploadedFileFactoryInterface::class));

        static::assertInstanceOf(UploadedFileFactoryInterface::class, $this->getUploadedFileFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Interop\Http\Factory\UploadedFileFactoryInterface is not set up.
     */
    public function testGetUploadedFileFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getUploadedFileFactory();
    }
}
