<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

interface Types
{
    /**
     * @var int
     */
    public const PLAIN = 0;

    /**
     * @var int
     */
    public const SERVICE = 1;

    /**
     * @var int
     */
    public const SINGLETON = 2;

    /**
     * @var int
     */
    public const LAZY = 3;

    /**
     * @var int
     */
    public const VALUE = 0;

    /**
     * @var int
     */
    public const IS_RESOLVED = 1;

    /**
     * @var int
     */
    public const BINDING_TYPE = 2;

    /**
     * @var int
     */
    public const ALIAS = 3;
}
