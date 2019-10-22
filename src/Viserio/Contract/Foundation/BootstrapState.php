<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Foundation;

interface BootstrapState extends Bootstrap
{
    public const TYPE_BEFORE = 'Before';
    public const TYPE_AFTER = 'After';

    /**
     * Returns the bootstrap type when this bootstrap should run, before or after a parent bootstrap class.
     *
     * @return string
     */
    public static function getType(): string;

    /**
     * Returns the bootstrap class for the BootstrapManger::addBeforeBootstrapping and BootstrapManger::addAfterBootstrapping function.
     *
     * @return string
     */
    public static function getBootstrapper(): string;
}
