<?php
declare(strict_types=1);
namespace Viserio\Component\Translation;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Viserio\Component\Contract\Parser\Traits\ParserAwareTrait;
use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Translation\Exception\RuntimeException;
use Viserio\Component\Contract\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Component\Contract\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;

class TranslationManager implements TranslationManagerContract, LoggerAwareInterface
{
    use ValidateLocaleTrait;
    use ParserAwareTrait;
    use LoggerAwareTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * MessageFormatter instance.
     *
     * @var \Viserio\Component\Contract\Translation\MessageFormatter
     */
    protected $formatter;

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $locale = 'en';

    /**
     * Default fallback for all languages.
     *
     * @var null|\Viserio\Component\Contract\Translation\MessageCatalogue
     */
    protected $defaultFallback;

    /**
     * Fallbacks for special languages.
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
     * Create a new Translation instance.
     *
     * @param \Viserio\Component\Contract\Translation\MessageFormatter $formatter
     */
    public function __construct(MessageFormatterContract $formatter)
    {
        $this->formatter = $formatter;
        $this->logger    = new NullLogger();
    }

    /**
     * Set directories.
     *
     * @param array $directories
     *
     * @return $this
     */
    public function setDirectories(array $directories): self
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
    public function addDirectory(string $directory): self
    {
        if (! \in_array($directory, $this->directories, true)) {
            $this->directories[] = self::normalizeDirectorySeparator($directory);
        }

        return $this;
    }

    /**
     * Import a language from file.
     *
     * @param string $file
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException
     *
     * @return $this
     */
    public function import(string $file): self
    {
        $loader = $this->getLoader();
        $loader->setDirectories($this->directories);

        $langFile = $loader->load($file);

        if (! isset($langFile['lang'])) {
            throw new InvalidArgumentException(\sprintf('File [%s] cant be imported. Key for language is missing.', $file));
        }

        $this->addMessageCatalogue(new MessageCatalogue($langFile['lang'], $langFile));

        return $this;
    }

    /**
     * Add message catalogue.
     *
     * @param \Viserio\Component\Contract\Translation\MessageCatalogue $messageCatalogue
     *
     * @return $this
     */
    public function addMessageCatalogue(MessageCatalogueContract $messageCatalogue): self
    {
        $locale = $messageCatalogue->getLocale();

        if ($fallback = $this->getLanguageFallback($messageCatalogue->getLocale())) {
            $messageCatalogue->addFallbackCatalogue($fallback);
        } elseif ($this->defaultFallback !== null) {
            $messageCatalogue->addFallbackCatalogue($this->defaultFallback);
        }

        $translation = new Translator($messageCatalogue, $this->formatter);

        $translation->setLogger($this->logger);

        $this->translations[$locale] = $translation;

        return $this;
    }

    /**
     * Set default fallback for all languages.
     *
     * @param \Viserio\Component\Contract\Translation\MessageCatalogue $fallback
     *
     * @return $this
     */
    public function setDefaultFallback(MessageCatalogueContract $fallback): self
    {
        $this->defaultFallback = $fallback;

        return $this;
    }

    /**
     * Get default fallback.
     *
     * @return null|\Viserio\Component\Contract\Translation\MessageCatalogue
     */
    public function getDefaultFallback(): ?MessageCatalogueContract
    {
        return $this->defaultFallback;
    }

    /**
     * Set fallback for a language.
     *
     * @param string                                                   $lang
     * @param \Viserio\Component\Contract\Translation\MessageCatalogue $fallback
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function setLanguageFallback(string $lang, MessageCatalogueContract $fallback): self
    {
        $this->langFallback[$lang] = $fallback;

        return $this;
    }

    /**
     * Get fallback for a language.
     *
     * @param string $lang
     *
     * @return null|\Viserio\Component\Contract\Translation\MessageCatalogue
     */
    public function getLanguageFallback(string $lang): ?MessageCatalogueContract
    {
        if (isset($this->langFallback[$lang])) {
            return $this->langFallback[$lang];
        }

        return null;
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
    public function setLocale(string $locale): self
    {
        self::assertValidLocale($locale);

        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslator(string $locale = null): TranslatorContract
    {
        $lang = $locale ?? $this->locale;

        if (isset($this->translations[$lang])) {
            return $this->translations[$lang];
        }

        throw new RuntimeException(\sprintf('Translator for [%s] doesn\'t exist.', $lang));
    }
}
