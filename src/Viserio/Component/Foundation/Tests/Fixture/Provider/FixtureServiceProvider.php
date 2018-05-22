<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Fixture\Provider;

use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;

class FixtureServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }
}
