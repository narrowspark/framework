<?php
namespace Viserio\Contracts\Translation;

interface Translator
{
    /**
     * Gets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @return string
     */
    public function getLocale();

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param string $defaultLang A string representing the default language to translate into. (e.g. 'en').
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return self
     */
    public function setLocale(string $defaultLang): Translator;

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function trans(
        string $id,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ): string;

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param int         $number     The number to use to find the indice of the message
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    public function transChoice(
        string $id,
        int $number,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ): string;

    /**
     * Add helper.
     *
     * @param string   $name
     * @param callable $helper
     *
     * @return self
     */
    public function addHelper(string $name, callable $helper): Translator;

    /**
     * Apply helpers.
     *
     * @param string[] $translation
     * @param array    $helpers
     *
     * @throws \Exception
     *
     * @return array
     */
    public function applyHelpers(array $translation, array $helpers): array;

    /**
     * Add filter.
     *
     * @param string   $name
     * @param callable $filter
     */
    public function addFilter(string $name, callable $filter);

    /**
     * @param string|array $translation
     * @param array        $filters
     *
     * @return array
     */
    public function applyFilters($translation, array $filters): array;
}
