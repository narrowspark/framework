<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Proxy;

use Twig\Environment as TwigEnvironment;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Twig extends StaticalProxy
{
    /**
     * Returns the twig environment class name.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public static function getInstanceIdentifier(): string
    {
        return TwigEnvironment::class;
    }
}
