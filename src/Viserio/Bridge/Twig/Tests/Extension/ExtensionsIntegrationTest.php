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

namespace Viserio\Bridge\Twig\Tests\Extension;

use Mockery as Mock;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig\Test\IntegrationTestCase;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Contract\Session\Store as StoreContract;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contract\Translation\Translator as TranslatorContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ExtensionsIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (\PHP_OS_FAMILY === 'Windows') {
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
