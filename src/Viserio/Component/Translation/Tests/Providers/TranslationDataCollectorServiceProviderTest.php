<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\MessageSelector;
use Viserio\Component\Translation\PluralizationRules;
use Viserio\Component\Translation\Providers\TranslationDataCollectorServiceProvider;
use Viserio\Component\Translation\Translator;
use Viserio\Component\WebProfiler\Providers\WebProfilerServiceProvider;

class TranslationDataCollectorServiceProviderTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testProvider()
    {
        $catalogue = new MessageCatalogue('en', [
            'messages' => [
                'foo' => 'bar',
            ],
        ]);

        $catalogue->addFallbackCatalogue(new MessageCatalogue('fr', [
            'messages' => [
                'test' => 'bar',
            ],
        ]));

        $selector = new MessageSelector();
        $selector->setPluralization(new PluralizationRules());

        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(TranslatorContract::class, new Translator(
            $catalogue,
            $selector
        ));
        $container->register(new ConfigServiceProvider());
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new WebProfilerServiceProvider());
        $container->register(new TranslationDataCollectorServiceProvider());

        $container->get(RepositoryContract::class)->set('webprofiler', [
            'collector' => [
                'translation' => true,
            ],
        ]);

        self::assertInstanceOf(WebProfilerContract::class, $container->get(WebProfilerContract::class));
    }

    private function getRequest()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME_FLOAT')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('REQUEST_TIME')
            ->andReturn(false);

        return $request;
    }
}
