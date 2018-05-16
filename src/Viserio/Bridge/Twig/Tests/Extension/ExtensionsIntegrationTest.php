<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Mockery as Mock;
use Twig\Test\IntegrationTestCase;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;

/**
 * @group appveyor
 */
class ExtensionsIntegrationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) === 'win') {
            $this->markTestSkipped('Test is skipped on windows.');
        }

        if (! \extension_loaded('xdebug')) {
            $this->markTestSkipped('Test is skipped if xdebug is not activated.');
        }
    }

    public function tearDown(): void
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
            new DumpExtension(),
        ];
    }

    public function getFixturesDir(): string
    {
        return __DIR__ . '/../Fixture/';
    }

    public function getLegacyTests()
    {
        return $this->getTests('testLegacyIntegration');
    }

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
