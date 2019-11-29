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

namespace Viserio\Component\Translation\Tests\Container\Provider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\OptionsResolver\Container\Provider\OptionsResolverServiceProvider;
use Viserio\Component\Parser\Container\Provider\ParserServiceProvider;
use Viserio\Component\Translation\Container\Provider\TranslationServiceProvider;
use Viserio\Component\Translation\TranslationManager;
use Viserio\Contract\Translation\Translator as TranslatorContract;

/**
 * @internal
 *
 * @small
 */
final class TranslatorServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /** @var \org\bovigo\vfs\vfsStreamAbstractContent */
    private $file;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);

return [
    "lang" => "en",
    "message" => [
        "Hallo" => "hallo",
    ]
];
            '
        )->at($this->root);

        parent::setUp();
    }

    public function testProvider(): void
    {
        $this->container->set(PsrLoggerInterface::class, Mockery::mock(PsrLoggerInterface::class));

        self::assertInstanceOf(TranslationManager::class, $this->container->get(TranslationManager::class));
        self::assertInstanceOf(TranslatorContract::class, $this->container->get('translator'));
        self::assertInstanceOf(TranslatorContract::class, $this->container->get(TranslatorContract::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', [
            'viserio' => [
                'translation' => [
                    'locale' => 'en',
                    'files' => $this->file->url(),
                    'directories' => [
                        __DIR__,
                    ],
                ],
            ],
        ]);

        $containerBuilder->setParameter('container.dumper.inline_factories', true);
        $containerBuilder->setParameter('container.dumper.inline_class_loader', false);
        $containerBuilder->setParameter('container.dumper.as_files', true);

        $containerBuilder->singleton(PsrLoggerInterface::class)
            ->setSynthetic(true);

        $containerBuilder->register(new OptionsResolverServiceProvider());
        $containerBuilder->register(new TranslationServiceProvider());
        $containerBuilder->register(new ParserServiceProvider());
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
