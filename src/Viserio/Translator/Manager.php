<?php
namespace Viserio\Translator;

use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Contracts\Cache\Factory as CacheContract;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Contracts\Translator\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contracts\Translator\NotFoundResourceException;
use Viserio\Filesystem\FileLoader;

class Manager
{
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
     * Translation logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
     * @param MessageSelector    $messageSelector
     */
    public function __construct(
        FileLoader $fileloader,
        MessageSelector $messageSelector
    ) {
        $this->loader        = $fileloader;
        $this->pluralization = new PluralizationRules();

        $messageSelector->setPluralization($this->pluralization);
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
     * Sets logger.
     *
     * @param PsrLoggerInterface $cache
     *
     * @return self
     */
    public function setLogger(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Returns the set logger.
     *
     * @return PsrLoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
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
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale($locale)
    {
        if (preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }
}
