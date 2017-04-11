<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Translation\TranslationManager;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;
use Viserio\Component\Foundation\Events\LocaleChangedEvent;

class Application extends Container implements ApplicationContract
{
    /**
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    protected $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     *
     * @var string
     */
    protected $environmentPath;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Create a new application instance.
     *
     * Let's start make magic!
     */
    public function __construct()
    {
        parent::__construct();

        $this->registerBaseServiceProviders();

        $this->registerCacheFilePaths();

        $this->registerBaseBindings();

        $config = $this->get(RepositoryContract::class);
        $config->set(
            'viserio.app.maintenance',
            file_exists($config->get('path.storage') . '/framework/down'),
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->get(EventManagerContract::class)->trigger(new BootstrappingEvent($bootstrapper, $this));

            $this->make($bootstrapper)->bootstrap($this);

            $this->get(EventManagerContract::class)->trigger(new BootstrappedEvent($bootstrapper, $this));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): ApplicationContract
    {
        $this->get(RepositoryContract::class)->set('viserio.app.locale', $locale);

        if ($this->has(TranslationManager::class)) {
            $this->get(TranslationManager::class)->setLocale($locale);
        }

        $this->get(EventManagerContract::class)->trigger(new LocaleChangedEvent($this, $locale));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocale(string $locale): bool
    {
        return $this->getLocale() == $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale(): string
    {
        return $this->get(RepositoryContract::class)->get('viserio.app.fallback_locale');
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale(string $locale): bool
    {
        return in_array($locale, $this->get(RepositoryContract::class)->get('viserio.app.locales'));
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->get(RepositoryContract::class)->get('path.base');
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function useEnvironmentPath(string $path): ApplicationContract
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function loadEnvironmentFrom(string $file): ApplicationContract
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function environmentFilePath(): string
    {
        return $this->environmentPath() . '/' . $this->environmentFile();
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = $_SERVER['argv'] ?? null;

        $this->instance('env', $this->get(EnvironmentDetector::class)->detect($callback, $args));

        return $this->get('env');
    }

    /**
     * Set needed cache paths to our config manager.
     *
     * @return void
     */
    protected function registerCacheFilePaths(): void
    {
        $config = $this->get(RepositoryContract::class);

        $config->set('patch.cached.config', $config->get('path.storage') . '/framework/cache/config.php');
    }
}
