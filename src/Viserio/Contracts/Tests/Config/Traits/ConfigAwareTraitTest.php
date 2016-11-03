<?php
declare(strict_types=1);
namespace Viserio\Contracts\Config\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager;
use Viserio\Contracts\Config\Traits\ConfigAwareTrait;

class ConfigAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use ConfigAwareTrait;

    public function testGetAndSetConfig()
    {
        $this->setConfig($this->mock(Manager::class));

        $this->assertInstanceOf(Manager::class, $this->getConfig());
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
