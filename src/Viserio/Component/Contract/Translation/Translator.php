<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Translation;

interface Translator
{
    public const MESSAGE_DEFINED         = 0;
    public const MESSAGE_MISSING         = 1;
    public const MESSAGE_EQUALS_FALLBACK = 2;

    /**
     * Gets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param string $locale A string representing the default language to translate into. (e.g. 'en').
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return $this
     */
    public function setLocale(string $locale): Translator;

    /**
     * Get the message selector instance.
     *
     * @return \Viserio\Component\Contract\Translation\MessageFormatter
     */
    public function getFormatter(): MessageFormatter;

    /**
     * Get the message catalogue.
     *
     * @return \Viserio\Component\Contract\Translation\MessageCatalogue
     */
    public function getCatalogue(): MessageCatalogue;

    /**
     * Translates the given message.
     *
     * @param string $id         The message id
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function trans(
        string $id,
        array $parameters = [],
        string $domain = 'messages'
    ): string;

    /**
     * Add helper.
     *
     * @param string   $name
     * @param callable $helper
     *
     * @return $this
     */
    public function addHelper(string $name, callable $helper): Translator;

    /**
     * Add filter.
     *
     * @param callable $filter
     *
     * @return $this
     */
    public function addFilter(callable $filter): Translator;
}
