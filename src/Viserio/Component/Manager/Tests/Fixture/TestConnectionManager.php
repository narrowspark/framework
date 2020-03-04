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

use Viserio\Component\Manager\AbstractConnectionManager;

class TestConnectionManager extends AbstractConnectionManager
{
    protected function createTestConnection($config = null)
    {
        return true;
    }

    protected function createFooConnection($config = null)
    {
        return new class() {
            public function getName(): string
            {
                return 'manager';
            }
        };
    }

    /**
     * Get the configuration name.
     */
    protected static function getConfigName(): string
    {
        return 'connection';
    }
}
