<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation;

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
     * @return \Viserio\Component\Contracts\Translation\MessageSelector
     */
    public function getSelector(): MessageSelector;

    /**
     * Get the message catalogue.
     *
     * @return \Viserio\Component\Contracts\Translation\MessageCatalogue
     */
    public function getCatalogue(): MessageCatalogue;

    /**
     * Translates the given message.
     *
     * @param string  $id         The message id (may also be an object that can be cast to string)
     * @param array   $parameters An array of parameters for the message
     * @param string  $domain     The domain for the message or null to use the default
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
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string               $id         The message id (may also be an object that can be cast to string)
     * @param int|array|\Countable $number     The number to use to find the indice of the message
     * @param array                $parameters An array of parameters for the message
     * @param string               $domain     The domain for the message or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function transChoice(
        string $id,
        $number,
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
