<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Discovery\Configurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Narrowspark\Discovery\Common\Contract\Package as PackageContract;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\Discovery\Configurator\ProjectConfigurator;

class ProjectConfiguratorTest extends MockeryTestCase
{
    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\NullIo
     */
    private $ioMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer();
        $this->ioMock   = $this->mock(IOInterface::class);

        ProjectConfigurator::$isTest = true;
    }

    public function testConfigure(): void
    {
        $configurator = new ProjectConfigurator($this->composer, $this->ioMock, []);
        $package      = $this->mock(PackageContract::class);

//        $configurator->configure($package);
    }
}
