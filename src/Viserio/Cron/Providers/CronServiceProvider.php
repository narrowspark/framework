<?php
declare(strict_types=1);
namespace Viserio\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class CronServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.cron';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [];
    }
}
