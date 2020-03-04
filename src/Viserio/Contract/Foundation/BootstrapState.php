<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Foundation;

interface BootstrapState extends Bootstrap
{
    public const TYPE_BEFORE = 'Before';
    public const TYPE_AFTER = 'After';

    /**
     * Returns the bootstrap type when this bootstrap should run, before or after a parent bootstrap class.
     */
    public static function getType(): string;

    /**
     * Returns the bootstrap class for the BootstrapManger::addBeforeBootstrapping and BootstrapManger::addAfterBootstrapping function.
     */
    public static function getBootstrapper(): string;
}
