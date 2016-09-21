<?php
declare(strict_types=1);
namespace Viserio\Validation\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Validator extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'validator';
    }
}
