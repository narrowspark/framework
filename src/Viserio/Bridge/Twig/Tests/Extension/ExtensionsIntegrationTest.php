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

use Mockery as Mock;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig\Test\IntegrationTestCase;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Session\Store as StoreContract;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contract\Translation\Translator as TranslatorContract;

/**
 * @group appveyor
 *
 * @internal
 *
 * @small
 */
final class ExtensionsIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (\stripos(\PHP_OS, 'win') === 0) {
            self::markTestSkipped('Test is skipped on windows.');
        }

        if (! \extension_loaded('xdebug')) {
            self::markTestSkipped('Test is skipped if xdebug is not activated.');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Verify Mockery expectations.
        Mock::close();
    }

    public function getExtensions(): array
    {
        return [
            new SessionExtension($this->getSessionMock()),
            new StrExtension(),
            new ConfigExtension($this->getConfigMock()),
            new TranslatorExtension($this->getTranslatorMock()),
            new DumpExtension(new VarCloner(), new HtmlDumper()),
        ];
    }

    public function getFixturesDir(): string
    {
        return \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR;
    }

    public function getLegacyTests(): array
    {
        return $this->getTests('testLegacyIntegration');
    }

    /**
     * @return \Mockery\MockInterface|\Viserio\Contract\Session\Store
     */
    private function getSessionMock()
    {
        $session = Mock::mock(StoreContract::class);
        $session->shouldReceive('get')
            ->with('test')
            ->andReturn('test');
        $session->shouldReceive('has')
            ->with('test')
            ->andReturn(true);
        $session->shouldReceive('getToken')
            ->andReturn('18191ds198189d1as89');

        return $session;
    }

    /**
     * @return \Mockery\MockInterface|\Viserio\Contract\Config\Repository
     */
    private function getConfigMock()
    {
        $config = Mock::mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->with('test')
            ->andReturn('test');
        $config->shouldReceive('has')
            ->with('test')
            ->andReturn(true);

        return $config;
    }

    /**
     * @return \Mockery\MockInterface|\Viserio\Contract\Translation\TranslationManager
     */
    private function getTranslatorMock()
    {
        $translator = Mock::mock(TranslatorContract::class);
        $translator->shouldReceive('trans')
            ->with('test', [], 'messages')
            ->andReturn('test');
        $translator->shouldReceive('trans')
            ->with('{count,plural,=0{No candy left}one{Got # candy left}other{Got # candies left}}', ['count' => 1], 'messages')
            ->andReturn('Got one candy left');

        $manager = Mock::mock(TranslationManagerContract::class);
        $manager->shouldReceive('getTranslator')
            ->andReturn($translator);

        return $manager;
    }
}
