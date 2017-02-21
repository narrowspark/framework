<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Log\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\Log\Traits\LoggerAwareTrait;

class LoggerAwareTraitTest extends MockeryTestCase
{
    use LoggerAwareTrait;

    public function testGetAndSetLogger()
    {
        $this->setLogger($this->mock(PsrLoggerInterface::class));

        self::assertInstanceOf(PsrLoggerInterface::class, $this->getLogger());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Logger is not set up.
     */
    public function testGetLoggerThrowExceptionIfLoggerIsNotSet()
    {
        $this->getLogger();
    }
}
