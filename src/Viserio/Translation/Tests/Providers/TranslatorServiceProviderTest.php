<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Parsers\Providers\ParsersServiceProvider;
use Viserio\Translation\Providers\TranslatorServiceProvider;
use Viserio\Translation\TranslationManager;

class TranslatorServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new TranslatorServiceProvider());
        $container->register(new ParsersServiceProvider());
        $container->register(new ConfigServiceProvider());

        $container->get('config')->set('translation', [
            'locale' => 'en',
            'path.lang' => __DIR__ . '/../Fixture/en.php',
        ]);

        $this->assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
        $this->assertInstanceOf(TranslatorContract::class, $container->get('translator'));
        $this->assertInstanceOf(TranslatorContract::class, $container->get(TranslatorContract::class));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new ParsersServiceProvider());
        $container->register(new TranslatorServiceProvider());

        $container->instance('options', [
            'locale' => 'en',
            'path.lang' => '../Fixture/en.php',
            'directories' => [
                __DIR__,
            ],
        ]);

        $this->assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new ParsersServiceProvider());
        $container->register(new TranslatorServiceProvider());

        $container->instance('viserio.translation.options', [
            'locale' => 'en',
            'path.lang' => '../Fixture/en.php',
        ]);
        $container->instance('viserio.parsers.options', [
            'directories' => [
                __DIR__,
            ],
        ]);

        $this->assertInstanceOf(TranslationManager::class, $container->get(TranslationManager::class));
    }
}
