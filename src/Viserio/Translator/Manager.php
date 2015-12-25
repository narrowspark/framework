<?php
namespace Viserio\Translator;

use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Contracts\Cache\Factory as CacheContract;
use Viserio\Contracts\Translator\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contracts\Translator\NotFoundResourceException;
use Viserio\Contracts\Translator\Translator as TranslatorContract;
use Viserio\Filesystem\FileLoader;
use Viserio\Translator\Traits\FiltersTrait;
use Viserio\Translator\Traits\HelpersTrait;
use Viserio\Translator\Traits\ReplacementTrait;
use Viserio\Translator\Traits\TranslateTrait;
use Viserio\Support\Manager as SupportManager;

class Manager extends SupportManager implements TranslatorContract
{
    //Register all needed traits
    use HelpersTrait, FiltersTrait, ReplacementTrait, TranslateTrait;

    /**
     * Messages loaded by the translator.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * FileLoader instance.
     *
     * @var \Viserio\Filesystem\FileLoader
     */
    protected $loader;

    /**
     * PluralizationRules instance.
     *
     * @var \Viserio\Translator\PluralizationRules
     */
    protected $pluralization;

    /**
     * MessageSelector instance.
     *
     * @var \Viserio\Translator\MessageSelector
     */
    protected $messageSelector;

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale = 'en';

    /**
     * Locale to use as fallback if there is no translation.
     *
     * @var array
     */
    protected $fallbackLocales = [];

    /**
     * Catalogues.
     *
     * @var MessageCatalogueInterface[]
     */
    protected $catalogues = [];

    /**
     * Translation cache.
     *
     * @var \Viserio\Contracts\Cache\Factory
     */
    protected $cache;

    /**
     * Event manager for triggering translator events.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $events;

    /**
     * Creat new Translator instance.
     *
     * @param FileLoader         $fileloader
     * @param PluralizationRules $pluralization
     * @param MessageSelector    $messageSelector
     */
    public function __construct(
        FileLoader $fileloader,
        PluralizationRules $pluralization,
        MessageSelector $messageSelector
    ) {
        $this->loader        = $fileloader;
        $this->pluralization = $pluralization;

        $messageSelector->setPluralization($pluralization);
        $this->messageSelector = $messageSelector;
    }

    /**
     * Add message catalogue.
     *
     * @param MessageCatalogueContract $messageCatalogue
     * @param string|null              $locale
     *
     * @return $this
     */
    public function addMessage(MessageCatalogueContract $messageCatalogue, $locale = null)
    {
        $locale = $locale === null ? $messageCatalogue->getLocale() : $locale;
        $this->messages[$locale] = $messageCatalogue;

        return $this;
    }

    /**
     * Load the given configuration group.
     *
     * @param string      $file
     * @param string|null $group
     * @param string|null $environment
     * @param string|null $namespace
     */
    public function bind($file, $group = null, $environment = null, $namespace = null)
    {
        $langFile = $this->loader->load($file, $group, $environment, $namespace);

        $this->addMessage(new MessageCatalogue($langFile['lang'], (array) $langFile), $langFile['lang']);
    }

    /**
     * Gets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->assertValidLocale($locale);

        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the fallback locale.
     *
     * @return array
     */
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }

    /**
     * Sets the fallback locales.
     *
     * @param array $locales The fallback locales
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     *
     * @api
     */
    public function setFallbackLocales(array $locales)
    {
        foreach ($locales as $locale) {
            $this->assertValidLocale($locale);
        }

        $this->fallbackLocales = $locales;
    }

    /**
     * Sets a cache.
     *
     * @param CacheContract $cache
     *
     * @return self
     */
    public function setCache(CacheContract $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Returns the set cache.
     *
     * @return CacheContract
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Sets a event.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event
     *
     * @return self
     */
    public function setEvent(EventDispatcherInterface $event)
    {
        $this->events = $event;

        return $this;
    }

    /**
     * Returns the event instance.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEvent()
    {
        return $this->events;
    }

    /**
     * Returns the pluralization instance.
     *
     * @return \Viserio\Translator\PluralizationRules
     */
    public function getPluralization()
    {
        return $this->pluralization;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->initializeCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }

    /**
     * Initialize catalogue.
     *
     * @param string $locale
     *
     * @throws NotFoundResourceException
     * @throws \Exception
     */
    protected function initializeCatalogue($locale)
    {
        $this->assertValidLocale($locale);

        try {
            //ToDo add fallback
        } catch (NotFoundResourceException $e) {
            if (!$this->computeFallbackLocales($locale)) {
                throw $e;
            }
        }

        $this->loadFallbackCatalogues($locale);
    }

    /**
     * Load fallback catalogues.
     *
     * @param string $locale
     */
    private function loadFallbackCatalogues($locale)
    {
        $current = $this->catalogues[$locale];

        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                //ToDo add fallback
            }

            $current->addFallbackCatalogue($this->catalogues[$fallback]);
            $current = $this->catalogues[$fallback];
        }
    }

    /**
     * Compute fallback locales.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function computeFallbackLocales($locale)
    {
        $locales = [];
        foreach ($this->fallbackLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }

            $locales[] = $fallback;
        }

        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }

        return array_unique($locales);
    }

    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale($locale)
    {
        if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
            throw new InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }
}
