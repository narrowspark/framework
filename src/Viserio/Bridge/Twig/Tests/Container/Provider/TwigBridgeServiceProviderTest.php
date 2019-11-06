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

namespace Viserio\Bridge\Twig\Tests\Provider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Twig\Environment as TwigEnvironment;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Bridge\Twig\Container\Provider\TwigBridgeServiceProvider;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Session\Store as StoreContract;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;

/**
 * @internal
 *
 * @small
 */
final class TwigBridgeServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    protected const DUMP_CLASS_CONTAINER = false;

    public function testProvider(): void
    {
        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        self::assertInstanceOf(TwigEnvironment::class, $this->container->get(TwigEnvironment::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(DebugCommand::getDefaultName()));
        self::assertTrue($console->has(LintCommand::getDefaultName()));
    }

    public function testProviderWithAllExtensions(): void
    {
        $this->containerBuilder->singleton(StoreContract::class)
            ->setSynthetic(true);
        $this->containerBuilder->singleton(RepositoryContract::class)
            ->setSynthetic(true);
        $this->containerBuilder->singleton(TranslationManagerContract::class)
            ->setSynthetic(true);
        $this->containerBuilder->singleton(Lexer::class)
            ->setSynthetic(true);

        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        $this->container->set(StoreContract::class, Mockery::mock(StoreContract::class));
        $this->container->set(RepositoryContract::class, Mockery::mock(RepositoryContract::class));
        $this->container->set(TranslationManagerContract::class, Mockery::mock(TranslationManagerContract::class));
        $this->container->set(Lexer::class, Mockery::mock(Lexer::class));

        $twig = $this->container->get(TwigEnvironment::class);

        self::assertInstanceOf(TwigEnvironment::class, $twig);
        self::assertInstanceOf(SessionExtension::class, $twig->getExtension(SessionExtension::class));
        self::assertInstanceOf(TranslatorExtension::class, $twig->getExtension(TranslatorExtension::class));
        self::assertInstanceOf(ConfigExtension::class, $twig->getExtension(ConfigExtension::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(DebugCommand::getDefaultName()));
        self::assertTrue($console->has(LintCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->singleton(TwigEnvironment::class)
            ->addArgument(new ArrayLoader([]))
            ->setPublic(true);

        $containerBuilder->register(new TwigBridgeServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
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
