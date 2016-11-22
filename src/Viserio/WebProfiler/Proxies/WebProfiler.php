<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class WebProfiler extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'web_profiler';
    }
}
