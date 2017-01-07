<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use StdClass;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Foundation\Application;
use Viserio\Foundation\Bootstrap\DetectEnvironment;

class ApplicationTest extends TestCase
{
    use MockeryTrait;

    public function testBootstrap()
    {
        $app = $this->getApplication();
        $app->bootstrapWith([
            DetectEnvironment::class,
        ]);

        self::assertTrue($app->hasBeenBootstrapped());
    }

    public function testSetLocaleSetsLocaleAndFiresLocaleChangedEvent()
    {
        $app = $this->getApplication();

        $config = $this->mock(StdClass::class);
        $config->shouldReceive('set')
            ->once()
            ->with('app.locale', 'foo');
        $config->shouldReceive('get')
            ->twice()
            ->with('app.locale')
            ->andReturn('foo');

        $app->instance(RepositoryContract::class, $config);

        $trans = $this->mock(StdClass::class);
        $trans->shouldReceive('setLocale')
            ->once()
            ->with('foo');

        $app->instance(TranslationManagerContract::class, $trans);

        $events = $this->mock(StdClass::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with('locale.changed', ['foo']);

        $app->instance(EventManagerContract::class, $events);

        $app->setLocale('foo');

        self::assertSame('foo', $app->getLocale());
        self::assertFalse($app->isLocale('de'));
    }

    protected function getApplication()
    {
        $paths = [
            'app'      => __DIR__ . '/../app',
            'config'   => __DIR__ . '/../config',
            'routes'   => __DIR__ . '/../routes',
            'database' => __DIR__ . '/../database',
            'lang'     => __DIR__ . '/../resources/lang',
            'public'   => __DIR__ . '/../public',
            'base'     => __DIR__ . '/..',
            'storage'  => __DIR__ . '/../storage',
        ];

        return new Application($paths);
    }
}
