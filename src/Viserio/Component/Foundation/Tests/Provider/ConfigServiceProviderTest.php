<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Foundation\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Foundation\Provider\ConfigServiceProvider as FoundationConfigServiceProvider;

/**
 * @internal
 */
final class ConfigServiceProviderTest extends TestCase
{
    public function testGetExtensions(): void
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new FoundationConfigServiceProvider());

        $processors = $container->get(RepositoryContract::class)->getParameterProcessors();

        static::assertCount(1, $processors);
        static::assertInstanceOf(EnvParameterProcessor::class, $processors['env']);
    }
}
