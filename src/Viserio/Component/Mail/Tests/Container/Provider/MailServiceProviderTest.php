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

namespace Viserio\Component\Mail\Tests\Container\Provider;

use Mockery;
use Psr\Log\LoggerInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Events\Container\Provider\EventsServiceProvider;
use Viserio\Component\Filesystem\Container\Provider\FilesServiceProvider;
use Viserio\Component\Mail\Container\Provider\MailServiceProvider;
use Viserio\Component\Mail\MailManager;
use Viserio\Component\Mail\TransportFactory;
use Viserio\Component\View\Container\Provider\ViewServiceProvider;
use Viserio\Contract\Mail\Mailer as MailerContract;
use Viserio\Contract\Queue\QueueConnector as QueueContract;

/**
 * @internal
 *
 * @small
 */
final class MailServiceProviderTest extends AbstractContainerTestCase
{
    protected const DUMP_CLASS_CONTAINER = false;

    public function testProvider(): void
    {
        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->setParameter('container.dumper.inline_factories', true);
        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', false);
        $this->containerBuilder->setParameter('container.dumper.as_files', true);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        $this->container->set(LoggerInterface::class, Mockery::mock(LoggerInterface::class));

        self::assertInstanceOf(TransportFactory::class, $this->container->get(TransportFactory::class));
        self::assertInstanceOf(MailManager::class, $this->container->get(MailManager::class));
        self::assertInstanceOf(MailerContract::class, $this->container->get(MailerContract::class));
        self::assertInstanceOf(MailerContract::class, $this->container->get('mailer'));
    }

    /**
     * @ToDo fix #394
     *    public function testProviderWithQueue(): void
     *    {
     *        $container = new Container();
     *        $container->register(new FilesServiceProvider());
     *        $container->register(new ViewServiceProvider());
     *        $container->register(new MailServiceProvider());
     *
     *        $container->get(RepositoryContract::class)->setArray([
     *            'viserio' => [
     *                'mail' => [
     *                    'connections' => [],
     *                ],
     *                'view' => [
     *                    'paths'      => [__DIR__],
     *                    'extensions' => ['php'],
     *                ],
     *            ],
     *        ]);
     *        $container->bind(QueueContract::class, $this->getMockBuilder(QueueContract::class)->getMock());
     *
     *        self::assertInstanceOf(QueueMailer::class, $container->get(MailerContract::class));
     *        self::assertInstanceOf(QueueMailer::class, $container->get('mailer'));
     *    }
     *
     * @param ContainerBuilder $containerBuilder
     */

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', [
            'viserio' => [
                'mail' => [
                    'connections' => [],
                ],
                'view' => [
                    'paths' => [__DIR__],
                    'extensions' => ['php'],
                ],
            ],
        ]);

        $containerBuilder->register(new FilesServiceProvider());
        $containerBuilder->register(new ViewServiceProvider());
        $containerBuilder->register(new EventsServiceProvider());
        $containerBuilder->register(new MailServiceProvider());

        $containerBuilder->singleton(LoggerInterface::class)
            ->setSynthetic(true);
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
