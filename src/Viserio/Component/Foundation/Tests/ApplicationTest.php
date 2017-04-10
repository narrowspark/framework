<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Foundation\Application;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;

class ApplicationTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $app = $this->getApplication();
        $app->bootstrapWith([
            LoadEnvironmentVariables::class,
        ]);

        self::assertTrue($app->hasBeenBootstrapped());
    }

    public function testSetLocaleSetsLocaleAndFiresLocaleChangedEvent()
    {
        $app = $this->getApplication();

        $config = $this->mock(stdClass::class);
        $config->shouldReceive('set')
            ->once()
            ->with('app.locale', 'foo');
        $config->shouldReceive('get')
            ->twice()
            ->with('app.locale')
            ->andReturn('foo');

        $app->instance(RepositoryContract::class, $config);

        $trans = $this->mock(stdClass::class);
        $trans->shouldReceive('setLocale')
            ->once()
            ->with('foo');

        $app->instance(TranslationManagerContract::class, $trans);

        $events = $this->mock(stdClass::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with('locale.changed', $app, ['locale' => 'foo']);

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
