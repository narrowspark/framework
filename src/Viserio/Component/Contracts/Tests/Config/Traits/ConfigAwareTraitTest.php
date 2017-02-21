<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Config\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Config\Traits\ConfigAwareTrait;

class ConfigAwareTraitTest extends MockeryTestCase
{
    use ConfigAwareTrait;

    public function testGetAndSetConfig()
    {
        $this->setConfig($this->mock(RepositoryContract::class));

        self::assertInstanceOf(RepositoryContract::class, $this->getConfig());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Config is not set up.
     */
    public function testGetConfigThrowExceptionIfConfigIsNotSet()
    {
        $this->getConfig();
    }
}
