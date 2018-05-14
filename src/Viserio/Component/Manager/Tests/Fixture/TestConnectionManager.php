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
     *
     * @return string
     */
    protected static function getConfigName(): string
    {
        return 'connection';
    }
}
