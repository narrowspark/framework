<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Proxie;

use Viserio\Component\Contracts\Validation\Validator as ValidatorContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

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
