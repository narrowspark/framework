<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Config\Traits\ConfigAwareTrait;

/**
 * @internal
 */
final class ConfigAwareTraitTest extends MockeryTestCase
{
    use ConfigAwareTrait;

    public function testGetAndSetConfig(): void
    {
        $this->setConfig($this->mock(RepositoryContract::class));

        static::assertInstanceOf(RepositoryContract::class, $this->getConfig());
    }

    public function testGetConfigThrowExceptionIfConfigIsNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Config is not set up.');

        $this->getConfig();
    }
}
