<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Twig_Test_IntegrationTestCase;
use Viserio\Bridge\Twig\Extensions\ConfigExtension;
use Viserio\Bridge\Twig\Extensions\DumpExtension;
use Viserio\Bridge\Twig\Extensions\SessionExtension;
use Viserio\Bridge\Twig\Extensions\StrExtension;
use Viserio\Bridge\Twig\Extensions\TranslatorExtension;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

// class ExtensionsIntegrationTest extends Twig_Test_IntegrationTestCase
// {
//     use MockeryTrait;

//     public function tearDown()
//     {
//         parent::tearDown();

//         $this->allowMockingNonExistentMethods(true);

//         // Verify Mockery expectations.
//         Mock::close();
//     }

//     public function getExtensions()
//     {
//         return [
//             new SessionExtension($this->getSessionMock()),
//             new StrExtension(),
//             new ConfigExtension($this->getConfigMock()),
//             new DumpExtension(),
//             new TranslatorExtension($this->getTranslatorMock()),
//         ];
//     }

//     public function getFixturesDir()
//     {
//         return __DIR__ . '/../Fixtures/';
//     }

//     public function getLegacyTests()
//     {
//         return $this->getTests('testLegacyIntegration', false);
//     }

//     private function getSessionMock()
//     {
//         $session = $this->mock(StoreContract::class);
//         $session->shouldReceive('get')
//             ->with('test')
//             ->andReturn('test');
//         $session->shouldReceive('has')
//             ->with('test')
//             ->andReturn(true);
//         $session->shouldReceive('getToken')
//             ->andReturn('18191ds198189d1as89');

//         return $session;
//     }

//     private function getConfigMock()
//     {
//         $config = $this->mock(RepositoryContract::class);
//         $config->shouldReceive('get')
//             ->with('test')
//             ->andReturn('test');
//         $config->shouldReceive('has')
//             ->with('test')
//             ->andReturn(true);

//         return $config;
//     }

//     private function getTranslatorMock()
//     {
//         $translator = $this->mock(TranslatorContract::class);
//         $translator->shouldReceive('trans')
//             ->with('test')
//             ->andReturn('test');
//         $translator->shouldReceive('transChoice')
//             ->with('{0} There are no apples|{1} There is one apple', 1)
//             ->andReturn('There is one apple');

//         return $translator;
//     }
// }
