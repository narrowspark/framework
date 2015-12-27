<?php
namespace Viserio\Contracts\Translator;

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
     * @return self
     */
    public function setLocale($defaultLang);

    /**
     * Translation/internationalization function with context support.
     *
     * @param string      $string  String to translate
     * @param mixed       $context String form or numeric count
     * @param array       $values  Param values to insert
     * @param string|null $locale  Target localeuage
     *
     * @return string
     */
    public function translate($string, $context, $values, $locale = null);

    /**
     * Returns the translation from the first reader where it exists, or the input string
     * if no translation is available.
     *
     * @param string $string
     * @param string $locale
     *
     * @return string
     */
    public function get($string, $locale);

    /**
     * Check if translation exists.
     *
     * @param string      $message
     * @param null|string $language
     *
     * @return bool
     */
    public function has($message, $language = null);

    /**
     * Add helper.
     *
     * @param string   $name
     * @param callable $helper
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
     * @return mixed
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
     * @return array
     */
    public function applyFilters($translation, array $filters);
}
