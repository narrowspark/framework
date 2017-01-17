<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Proxies;

use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\StaticalProxy\StaticalProxy;

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
