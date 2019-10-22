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

namespace Viserio\Component\Manager\Tests\Fixture;

use stdClass;
use Viserio\Component\Manager\AbstractManager;

class TestManager extends AbstractManager
{
    protected function createTestDriver($config = null)
    {
        return true;
    }

    protected function createConfigDriver($config)
    {
        return $config;
    }

    protected function createValueDriver($config)
    {
        return $config;
    }

    protected function createTestmanagerDriver($config)
    {
        return new stdClass();
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected static function getConfigName(): string
    {
        return 'test';
    }
}
