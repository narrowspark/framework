<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Proxy;

use Viserio\Component\Log\Writer as MonologWriter;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Log extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return MonologWriter::class;
    }
}
