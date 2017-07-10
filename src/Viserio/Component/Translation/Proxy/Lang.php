<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Proxy;

use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Lang extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return TranslatorContract::class;
    }
}
