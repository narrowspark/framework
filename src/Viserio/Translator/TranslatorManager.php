<?php
namespace Viserio\Translator;

use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Contracts\Cache\Factory as CacheContract;
use Viserio\Contracts\Translator\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contracts\Translator\NotFoundResourceException;
use Viserio\Filesystem\FileLoader;
use Viserio\Support\Traits\LoggerAwareTrait;
use Viserio\Translator\Traits\ValidateLocaleTrait;
use Viserio\Support\Manager;

class TranslatorManager extends Manager
{
    use ValidateLocaleTrait, LoggerAwareTrait;

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
     * @param MessageSelector    $messageSelector
     * @param PluralizationRules $pluralization
     */
    public function __construct(
        MessageSelector $messageSelector,
        PluralizationRules $pluralization
    ) {
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

        $translation = new Translator($messageCatalogue);

        $this->translations[$locale] = $translation;

        return $this;
    }

    /**
     * Load the given configuration group.
     *
     * @param string      $file
     * @param string|null $group
     * @param string|null $environment
     * @param string|null $namespace
     *
     * @return self
     */
    public function bind($file, $group = null, $environment = null, $namespace = null)
    {
        $langFile = $this->loader->load($file, $group, $environment, $namespace);

        $this->addMessage(new MessageCatalogue($langFile['lang'], (array) $langFile), $langFile['lang']);

        return $this;
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

    protected function getConfigName()
    {
        return 'translator';
    }

    /**
     * Logs for missing translations.
     *
     * @param string      $id
     * @param string|null $domain
     * @param string|null $locale
     */
    protected function log($id, $domain, $locale)
    {
        if ($domain === null) {
            $domain = 'messages';
        }

        $id = (string) $id;

        $catalogue = $this->translator->getCatalogue($locale);

        if ($catalogue->defines($id, $domain)) {
            return;
        }

        if ($catalogue->has($id, $domain)) {
            $this->logger->debug(
                'Translation use fallback catalogue.',
                ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]
            );
        } else {
            $this->logger->warning(
                'Translation not found.',
                ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]
            );
        }
    }
}
