<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Resolver;

use Viserio\Component\Contracts\Routing\ImplicitBinding as ImplicitBindingContract;

class ImplicitBinding implements ImplicitBindingContract
{
    /**
     * {@inheritdoc}
     */
    public function resolve(Route $route): Route
    {
    }
}
