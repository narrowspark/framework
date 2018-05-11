<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Fixtures\Provider;

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
