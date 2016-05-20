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
    public function setLocale($defaultLang);

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
    public function trans($id, array $parameters = [], $domain = null, $locale = null);

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
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null);

    /**
     * Add helper.
     *
     * @param string   $name
     * @param callable $helper
     *
     * @return self
     */
    public function addHelper($name, callable $helper);

    /**
     * Apply helpers.
     *
     * @param string[] $translation
     * @param array    $helpers
     *
     * @throws \Exception
     *
     * @return self
     */
    public function applyHelpers($translation, array $helpers);

    /**
     * Add filter.
     *
     * @param string   $name
     * @param callable $filter
     */
    public function addFilter($name, callable $filter);

    /**
     * @param array        $filters
     * @param string|array $translation
     *
     * @return self
     */
    public function applyFilters($translation, array $filters);
}
