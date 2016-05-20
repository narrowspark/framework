<?php
namespace Viserio\Translation;

use InvalidArgumentException;
use RuntimeException;
use Viserio\Contracts\Cache\Factory as CacheContract;
use Viserio\Contracts\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contracts\Translation\NotFoundResourceException;
use Viserio\Support\Traits\EventAwareTrait;
use Viserio\Support\Traits\FileLoaderAwareTrait;
use Viserio\Support\Traits\LoggerAwareTrait;
use Viserio\Translation\Traits\ValidateLocaleTrait;

class TranslationManager
{
    use ValidateLocaleTrait;
    use LoggerAwareTrait;
    use FileLoaderAwareTrait;
    use EventAwareTrait;

    /**
     * PluralizationRules instance.
     *
     * @var \Viserio\Translation\PluralizationRules
     */
    protected $pluralization;

    /**
     * MessageSelector instance.
     *
     * @var \Viserio\Translation\MessageSelector
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
     * Default fallback for all languages.
     *
     * @var MessageCatalogueContract
     */
    protected $defaultFallback;

    /**
     * Fallbacks for speziall languages.
     *
     * @var array
     */
    protected $langFallback = [];

    /**
     * Creat new Translation instance.
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
     * Import language from file.
     * Can be grouped together.
     *
     * @param string $file
     *
     * @return self
     */
    public function import($file)
    {
        $langFile = $this->loader->load($file);

        if (!isset($langFile['lang'])) {
            throw new RuntimeException(sprintf('File [%s] cant be imported.', $file));
        }

        $message = new MessageCatalogue($langFile['lang'], $langFile);

        if ($fallback = $this->getLanguageFallback($message->getLocale())) {
            $message->addFallbackCatalogue($fallback);
        } elseif ($fallback = $this->defaultFallback) {
            $message->addFallbackCatalogue($fallback);
        }

        $this->addMessage($message, $langFile['lang']);

        return $this;
    }

    /**
     * Set default fallback for all languages.
     *
     * @param MessageCatalogueContract $fallback
     *
     * @return self
     */
    public function setDefaultFallback(MessageCatalogueContract $fallback)
    {
        $this->defaultFallback = $fallback;

        return $this;
    }

    /**
     * Get default fallback.
     *
     * @return MessageCatalogueContract|null
     */
    public function getDefaultFallback()
    {
        return $this->defaultFallback;
    }

    /**
     * Set fallback for a language.
     *
     * @param stirng                   $lang
     * @param MessageCatalogueContract $fallback
     *
     * @return self
     */
    public function setLanguageFallback($lang, MessageCatalogueContract $fallback)
    {
        $this->langFallback[$lang] = $fallback;

        return $this;
    }

    /**
     * Get fallback for a language.
     *
     * @param stirng $lang
     *
     * @return MessageCatalogueContract|null
     */
    public function getLanguageFallback($lang)
    {
        if (isset($this->langFallback[$lang])) {
            return $this->langFallback[$lang];
        }

        return;
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
     * Returns the pluralization instance.
     *
     * @return \Viserio\Translation\PluralizationRules
     */
    public function getPluralization()
    {
        return $this->pluralization;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName()
    {
        return 'translation';
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
                'Translation use a fallback catalogue.',
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
