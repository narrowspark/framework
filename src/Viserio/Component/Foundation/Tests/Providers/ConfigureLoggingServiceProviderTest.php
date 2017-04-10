<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Writer;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConfigureLoggingServiceProviderTest extends MockeryTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithSingle()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer = $this->mock(Writer::class);
        $writer->shouldReceive('useFiles')
            ->once()
            ->with('/logs/narrowspark.log', 'debug');

        $container->instance(Writer::class, $writer);

        $container->instance('config', [
            'viserio' => [
                'app' => [
                    'path' => [
                        'storage' => '',
                    ],
                ],
            ],
        ]);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithDaily()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer = $this->mock(Writer::class);
        $writer->shouldReceive('useDailyFiles')
            ->once()
            ->with('/logs/narrowspark.log', 5, 'debug');

        $container->instance(Writer::class, $writer);
        $container->instance('config', [
            'viserio' => [
                'app' => [
                    'log' => [
                        'handler' => 'daily',
                    ],
                    'path' => [
                        'storage' => '',
                    ],
                ],
            ],
        ]);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithErrorlog()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer  = $this->mock(Writer::class);
        $handler = $this->mock(HandlerParser::class);
        $handler->shouldReceive('parseHandler')
            ->once();

        $container->instance(Writer::class, $writer);
        $container->instance(HandlerParser::class, $handler);
        $container->instance('config', [
            'viserio' => [
                'app' => [
                    'log' => [
                        'handler' => 'errorlog',
                    ],
                    'path' => [
                        'storage' => '',
                    ],
                ],
            ],
        ]);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithSyslog()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer  = $this->mock(Writer::class);
        $handler = $this->mock(HandlerParser::class);
        $handler->shouldReceive('parseHandler')
            ->once();

        $container->instance(Writer::class, $writer);
        $container->instance(HandlerParser::class, $handler);
        $container->instance('config', [
            'viserio' => [
                'app' => [
                    'log' => [
                        'handler' => 'syslog',
                    ],
                    'path' => [
                        'storage' => '',
                    ],
                ],
            ],
        ]);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }
}
