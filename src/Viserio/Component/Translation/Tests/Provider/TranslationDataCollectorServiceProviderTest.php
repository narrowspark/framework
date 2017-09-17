<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\Profiler\Provider\ProfilerServiceProvider;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\MessageCatalogue;
use Viserio\Component\Translation\Provider\TranslationDataCollectorServiceProvider;
use Viserio\Component\Translation\Translator;

class TranslationDataCollectorServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
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

        $container = new Container();
        $container->instance(ServerRequestInterface::class, $this->getRequest());
        $container->instance(TranslatorContract::class, new Translator(
            $catalogue,
            new IntlMessageFormatter()
        ));
        $container->register(new HttpFactoryServiceProvider());
        $container->register(new ProfilerServiceProvider());
        $container->register(new TranslationDataCollectorServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'profiler' => [
                    'enable'    => true,
                    'collector' => [
                        'translation' => true,
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(ProfilerContract::class, $container->get(ProfilerContract::class));
    }

    public function testProviderProfilerIsNull(): void
    {
        $container = new Container();
        $container->register(new TranslationDataCollectorServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'profiler' => [
                    'enable'    => true,
                    'collector' => [
                        'translation' => true,
                    ],
                ],
            ],
        ]);

        self::assertNull($container->get(ProfilerContract::class));
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
