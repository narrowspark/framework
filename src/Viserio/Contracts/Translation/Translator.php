<?php
namespace Viserio\Contracts\Translation;

interface Translator
{
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
     * @return self
     */
    public function setLocale(string $locale): Translator;

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
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function applyHelpers(array $translation, array $helpers);

    /**
     * Returns translation of a string. If no translation exists, the original string will be
     * returned. No parameters are replaced.
     *
     * @param string      $string
     * @param int         $count
     * @param string|null $locale
     *
     * @return string
     */
    public function plural(string $string, int $count = 0, $locale = null): string;

    /**
     * Add filter.
     *
     * @param string   $name
     * @param callable $filter
     *
     * @return self
     */
    public function addFilter(string $name, callable $filter): Translator;

    /**
     * Applay filter.
     *
     * @param string|array $translation
     * @param array        $filters
     *
     * @return array
     */
    public function applyFilters($translation, array $filters): array;

    /**
     * Add replacement for a existing translation.
     *
     * @param string $search
     * @param string $replacement
     *
     * @return self
     */
    public function addReplacement(string $search, string $replacement): Translator;

    /**
     * Remove replacements.
     *
     * @param string $search
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function removeReplacement(string $search): Translator;

    /**
     * Get all replacements.
     *
     * @return array
     */
    public function getReplacements(): array;
}
