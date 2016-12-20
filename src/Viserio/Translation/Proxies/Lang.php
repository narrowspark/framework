<?php
declare(strict_types=1);
namespace Viserio\Translation\Proxies;

use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\StaticalProxy\StaticalProxy;

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
