<?php
namespace Viserio\Translator;

use InvalidArgumentException;
use Viserio\Contracts\Cache\Factory as CacheContract;
use Viserio\Contracts\Translator\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contracts\Translator\NotFoundResourceException;
use Viserio\Events\Traits\EventAwareTrait;
use Viserio\Filesystem\FileLoader;
use Viserio\Parsers\Traits\FileLoaderAwareTrait;
use Viserio\Support\Manager;
use Viserio\Support\Traits\LoggerAwareTrait;
use Viserio\Translator\Traits\ValidateLocaleTrait;

class TranslatorManager extends Manager
{
    use ValidateLocaleTrait;
    use LoggerAwareTrait;
    use FileLoaderAwareTrait;
    use EventAwareTrait;

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
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
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
     * @param string      $file
     * @param string|null $group
     *
     * @return self
     */
    public function import($file, $group = null)
    {
        $langFile = $this->loader->load($file, $group);

        $this->addMessage(new MessageCatalogue($langFile['lang'], $langFile), $langFile['lang']);

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
