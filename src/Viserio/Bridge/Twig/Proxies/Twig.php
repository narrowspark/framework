<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Proxies;

use Twig\Environment as TwigEnvironment;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Twig extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return TwigEnvironment::class;
    }
}
