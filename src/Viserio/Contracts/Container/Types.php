<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container;

interface Types
{
    const PLAIN = 0;

    const SERVICE = 1;

    const SINGLETON = 2;

    const VALUE = 0;

    const IS_RESOLVED = 1;

    const BINDING_TYPE = 2;
}
