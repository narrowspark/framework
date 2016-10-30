<?php
declare(strict_types=1);
namespace Viserio\Validation\Proxies;

use Viserio\Contracts\Validation\Validator as ValidatorContract;
use Viserio\StaticalProxy\StaticalProxy;

class Validator extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return ValidatorContract::class;
    }
}
