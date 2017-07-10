<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Config\Traits\ConfigAwareTrait;

class ConfigAwareTraitTest extends MockeryTestCase
{
    use ConfigAwareTrait;

    public function testGetAndSetConfig(): void
    {
        $this->setConfig($this->mock(RepositoryContract::class));

        self::assertInstanceOf(RepositoryContract::class, $this->getConfig());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Config is not set up.
     */
    public function testGetConfigThrowExceptionIfConfigIsNotSet(): void
    {
        $this->getConfig();
    }
}
