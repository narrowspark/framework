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

namespace Viserio\Component\Mail\Tests\Container\Provider;

use Mockery;
use Psr\Log\LoggerInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Events\Container\Provider\EventsServiceProvider;
use Viserio\Component\Filesystem\Container\Provider\FilesystemServiceProvider;
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
 * @coversNothing
 */
final class MailServiceProviderTest extends AbstractContainerTestCase
{
    protected const DUMP_CLASS_CONTAINER = false;

    public function testProvider(): void
    {
        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->setParameter('viserio.container.dumper.inline_factories', true);
        $this->containerBuilder->setParameter('viserio.container.dumper.inline_class_loader', false);
        $this->containerBuilder->setParameter('viserio.container.dumper.as_files', true);

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
     *        $container->register(new FilesystemServiceProvider());
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

        $containerBuilder->register(new FilesystemServiceProvider());
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
