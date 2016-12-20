<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container;

interface Types
{
    public const PLAIN = 0;

    public const SERVICE = 1;

    public const SINGLETON = 2;

    public const VALUE = 0;

    public const IS_RESOLVED = 1;

    public const BINDING_TYPE = 2;
}
