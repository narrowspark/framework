<?php
declare(strict_types=1);
namespace Viserio\Pagination\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class PaginationServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.pagination';
}
