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
     */
    protected static function getConfigName(): string
    {
        return 'test';
    }
}
