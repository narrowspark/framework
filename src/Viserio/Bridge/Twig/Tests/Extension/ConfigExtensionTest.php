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

namespace Viserio\Bridge\Twig\Tests\Extension;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Contract\Config\Repository as RepositoryContract;

/**
 * @internal
 *
 * @small
 */
final class ConfigExtensionTest extends MockeryTestCase
{
    public function testGetFunctions(): void
    {
        $config = Mockery::mock(RepositoryContract::class);

        $extension = new ConfigExtension($config);
        $functions = $extension->getFunctions();

        self::assertEquals('config', $functions[0]->getName());
        self::assertEquals('get', $functions[0]->getCallable()[1]);

        self::assertEquals('config_get', $functions[1]->getName());
        self::assertEquals('get', $functions[1]->getCallable()[1]);

        self::assertEquals('config_has', $functions[2]->getName());
        self::assertEquals('has', $functions[2]->getCallable()[1]);
    }

    public function testGetName(): void
    {
        self::assertEquals(
            'Viserio_Bridge_Twig_Extension_Config',
            (new ConfigExtension(Mockery::mock(RepositoryContract::class)))->getName()
        );
    }
}
