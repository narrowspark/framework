<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Proxie;

use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
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
