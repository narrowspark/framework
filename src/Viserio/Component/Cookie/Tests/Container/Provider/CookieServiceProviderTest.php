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

namespace Viserio\Component\Cookie\Tests\Container\Provider;

use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Cookie\Container\Provider\CookieServiceProvider;
use Viserio\Component\Cookie\CookieJar;

/**
 * @internal
 *
 * @small
 */
final class CookieServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(CookieJar::class, $this->container->get(CookieJar::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('viserio', [
            'cookie' => [
                'domain' => '',
                'path' => '',
                'secure' => true,
            ],
        ]);
        $containerBuilder->register(new CookieServiceProvider());
        $containerBuilder->register(new ConfigServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
