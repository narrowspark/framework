<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Translation;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;
use Viserio\Contract\Parser\Traits\ParserAwareTrait;
use Viserio\Contract\Translation\Exception\InvalidArgumentException;
use Viserio\Contract\Translation\Exception\RuntimeException;
use Viserio\Contract\Translation\MessageCatalogue as MessageCatalogueContract;
use Viserio\Contract\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contract\Translation\Translator as TranslatorContract;

class TranslationManager implements LoggerAwareInterface, TranslationManagerContract
{
    use ValidateLocaleTrait;
    use ParserAwareTrait;
    use LoggerAwareTrait;

    /**
     * MessageFormatter instance.
     *
     * @var \Viserio\Contract\Translation\MessageFormatter
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
     * @var null|\Viserio\Contract\Translation\MessageCatalogue
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
     * @param \Viserio\Contract\Translation\MessageFormatter $formatter
     */
    public function __construct(MessageFormatterContract $formatter)
    {
        $this->formatter = $formatter;
        $this->logger = new NullLogger();
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
     * Get default fallback.
     *
     * @return null|\Viserio\Contract\Translation\MessageCatalogue
     */
    public function getDefaultFallback(): ?MessageCatalogueContract
    {
        return $this->defaultFallback;
    }

    /**
     * Set default fallback for all languages.
     *
     * @param \Viserio\Contract\Translation\MessageCatalogue $fallback
     *
     * @return $this
     */
    public function setDefaultFallback(MessageCatalogueContract $fallback): self
    {
        $this->defaultFallback = $fallback;

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
     * Add directory.
     *
     * @param string $directory
     *
     * @return $this
     */
    public function addDirectory(string $directory): self
    {
        if (! \in_array($directory, $this->directories, true)) {
            $this->directories[] = $directory;
        }

        return $this;
    }

    /**
     * Imports a language from given file path array or file path.
     *
     * @param array|string $filePaths
     *
     * @throws \Viserio\Contract\Translation\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     *
     * @return $this
     */
    public function import($filePaths): self
    {
        $this->loader->setDirectories($this->directories);

        $this->imports((array) $filePaths, $this->loader);

        return $this;
    }

    /**
     * Add message catalogue.
     *
     * @param \Viserio\Contract\Translation\MessageCatalogue $messageCatalogue
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
     * Set fallback for a language.
     *
     * @param string                                         $lang
     * @param \Viserio\Contract\Translation\MessageCatalogue $fallback
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
     * @return null|\Viserio\Contract\Translation\MessageCatalogue
     */
    public function getLanguageFallback(string $lang): ?MessageCatalogueContract
    {
        if (isset($this->langFallback[$lang])) {
            return $this->langFallback[$lang];
        }

        return null;
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

    /**
     * Imports a language from given file path array.
     *
     * @param array                           $filePaths
     * @param \Viserio\Contract\Parser\Loader $loader
     *
     * @throws \Viserio\Contract\Translation\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Parser\Exception\FileNotFoundException
     *
     * @return void
     */
    private function imports(array $filePaths, $loader): void
    {
        foreach ($filePaths as $filePath) {
            $langFile = $loader->load($filePath);

            if (! isset($langFile['lang'])) {
                throw new InvalidArgumentException(\sprintf('File [%s] cant be imported. Key for language is missing.', $filePath));
            }

            $this->addMessageCatalogue(new MessageCatalogue($langFile['lang'], $langFile));
        }
    }
}
