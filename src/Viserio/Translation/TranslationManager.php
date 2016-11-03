<?php
declare(strict_types=1);
namespace Viserio\Translation;

use RuntimeException;
use Viserio\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Contracts\Parsers\Traits\LoaderAwareTrait;
use Viserio\Contracts\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contracts\Translation\MessageSelector as MessageSelectorContract;
use Viserio\Contracts\Translation\PluralizationRules as PluralizationRulesContract;
use Viserio\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Translation\Traits\ValidateLocaleTrait;

class TranslationManager implements TranslationManagerContract
{
    use ValidateLocaleTrait;
    use LoaderAwareTrait;
    use LoggerAwareTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * PluralizationRules instance.
     *
     * @var \Viserio\Contracts\Translation\PluralizationRules
     */
    protected $pluralization;

    /**
     * MessageSelector instance.
     *
     * @var \Viserio\Contracts\Translation\MessageSelector
     */
    protected $messageSelector;

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale = 'en';

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
     * All directories to look for a file.
     *
     * @var array
     */
    protected $directories = [];

    /**
     * All added translations.
     *
     * @var array
     */
    protected $translations = [];

    /**
     * Creat new Translation instance.
     *
     * @param \Viserio\Contracts\Translation\PluralizationRules $pluralization
     * @param \Viserio\Contracts\Translation\MessageSelector    $messageSelector
     */
    public function __construct(
        PluralizationRulesContract $pluralization,
        MessageSelectorContract $messageSelector
    ) {
        $this->pluralization = $pluralization;

        $messageSelector->setPluralization($pluralization);
        $this->messageSelector = $messageSelector;
    }

    /**
     * Set directories
     *
     * @param array $directories
     *
     * @return $this
     */
    public function setDirectories(array $directories): TranslationManager
    {
        foreach ($directories as $directory) {
            $this->addDirectory($directory);
        }

        return $this;
    }

    /**
     * Get directories.
     *
     * @return array
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * Add directory.
     *
     * @param string $directory
     *
     * @return $this
     */
    public function addDirectory(string $directory): TranslationManager
    {
        if (! in_array($directory, $this->directories)) {
            $this->directories[] = self::normalizeDirectorySeparator($directory);
        }

        return $this;
    }

    /**
     * Import a language from file.
     *
     * @param string $file
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function import(string $file): TranslationManager
    {
        $loader = $this->getLoader();
        $loader->setDirectories($this->directories);

        $langFile = $loader->load($file);

        if (! isset($langFile['lang'])) {
            throw new RuntimeException(sprintf('File [%s] cant be imported. Key for language is missing.', $file));
        }

        $message = new MessageCatalogue($langFile['lang'], $langFile);

        $this->addMessageCatalogue($message);

        return $this;
    }

    /**
     * Add message catalogue.
     *
     * @param \Viserio\Contracts\Translation\MessageCatalogue $messageCatalogue
     *
     * @return $this
     */
    public function addMessageCatalogue(MessageCatalogueContract $messageCatalogue): TranslationManager
    {
        $locale = $messageCatalogue->getLocale();

        if ($fallback = $this->getLanguageFallback($messageCatalogue->getLocale())) {
            $messageCatalogue->addFallbackCatalogue($fallback);
        } elseif ($fallback = $this->defaultFallback) {
            $messageCatalogue->addFallbackCatalogue($fallback);
        }

        $translation = new Translator($messageCatalogue, $this->messageSelector);

        if ($this->logger !== null) {
            $translation->setLogger($this->logger);
        }

        $this->translations[$locale] = $translation;

        return $this;
    }

    /**
     * Set default fallback for all languages.
     *
     * @param \Viserio\Contracts\Translation\MessageCatalogue $fallback
     *
     * @return $this
     */
    public function setDefaultFallback(MessageCatalogueContract $fallback): TranslationManager
    {
        $this->defaultFallback = $fallback;

        return $this;
    }

    /**
     * Get default fallback.
     *
     * @return \Viserio\Contracts\Translation\MessageCatalogue
     */
    public function getDefaultFallback(): MessageCatalogueContract
    {
        return $this->defaultFallback;
    }

    /**
     * Set fallback for a language.
     *
     * @param string                                          $lang
     * @param \Viserio\Contracts\Translation\MessageCatalogue $fallback
     *
     * @return $this
     */
    public function setLanguageFallback(string $lang, MessageCatalogueContract $fallback)
    {
        $this->langFallback[$lang] = $fallback;

        return $this;
    }

    /**
     * Get fallback for a language.
     *
     * @param string $lang
     *
     * @return MessageCatalogueContract|null
     */
    public function getLanguageFallback(string $lang)
    {
        if (isset($this->langFallback[$lang])) {
            return $this->langFallback[$lang];
        }
    }

    /**
     * Gets the string dictating the default language.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale): TranslationManager
    {
        $this->assertValidLocale($locale);

        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns the pluralization instance.
     *
     * @return \Viserio\Translation\PluralizationRules
     */
    public function getPluralization(): PluralizationRulesContract
    {
        return $this->pluralization;
    }

    /**
     * Get a language translator instance.
     *
     * @param string|null $locale
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Translation\Translator
     */
    public function getTranslator(string $locale = null): TranslatorContract
    {
        $lang = $locale ?? $this->locale;

        if (isset($this->translations[$lang])) {
            return $this->translations[$lang];
        }

        throw new RuntimeException(sprintf('Translator for [%s] dont exist.', $lang));
    }
}
