<?php
declare(strict_types=1);
namespace Viserio\Contracts\Log\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Contracts\Log\Traits\LoggerAwareTrait;

class LoggerAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use LoggerAwareTrait;

    public function testGetAndSetLogger()
    {
        $this->setLogger($this->mock(PsrLoggerInterface::class));

        $this->assertInstanceOf(PsrLoggerInterface::class, $this->getLogger());
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
