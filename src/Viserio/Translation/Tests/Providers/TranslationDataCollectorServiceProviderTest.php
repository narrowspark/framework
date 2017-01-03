<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\Providers;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Translation\Providers\TranslationDataCollectorServiceProvider;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\Providers\WebProfilerServiceProvider;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\Translation\MessageCatalogue;
use Viserio\Translation\MessageSelector;
use Viserio\Translation\PluralizationRules;
use Viserio\Translation\Translator;
use Viserio\Contracts\Translation\Translator as TranslatorContract;

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
                'translation' => true
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
