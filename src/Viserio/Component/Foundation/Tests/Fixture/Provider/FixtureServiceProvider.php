<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Fixture\Provider;

use Interop\Container\ServiceProviderInterface;

class FixtureServiceProvider implements ServiceProviderInterface
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
