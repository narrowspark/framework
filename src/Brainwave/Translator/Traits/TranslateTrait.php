<?php

namespace Brainwave\Translator\Traits;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

/**
 * TranslateTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
trait TranslateTrait
{
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
    public function translate($string, $context, $values, $locale = null)
    {
        $locale = (null !== $locale) ? $this->assertValidLocale($locale) : $this->getLocale();

        if (is_numeric($context)) {
            // Get plural form
            $string = $this->plural($string, $context, $locale);
        } else {
            // Get custom form
            $string = $this->form($string, $context, $locale);
        }

        return empty($values) ? $string : strtr($string, $values);
    }

    /**
     * Returns specified form of a string translation. If no translation exists, the original string will be
     * returned. No parameters are replaced.
     *
     * @param string      $string
     * @param string|null $form   if null, looking for 'other' form, else the very first form
     * @param string|null $locale
     *
     * @return string
     */
    public function form($string, $form = null, $locale = null)
    {
        $this->assertValidLocale($locale);

        $translation = $this->get($string, $locale);

        if (is_array($translation)) {
            if (array_key_exists($form, $translation)) {
                return $translation[$form];
            } elseif (array_key_exists('other', $translation)) {
                return $translation['other'];
            }

            return reset($translation);
        }

        return $translation;
    }

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
    public function plural($string, $count = 0, $locale = null)
    {
        $this->assertValidLocale($locale);

        // Get the translation form key
        $form = $this->getPluralization()->get($count, $locale);

        // Return the translation for that form
        return $this->form($string, $form, $locale);
    }

    /**
     * Returns the translation from the first reader where it exists, or the input string
     * if no translation is available.
     *
     * @param string $string
     * @param string $locale
     *
     * @return string
     */
    protected function get($string, $locale)
    {
        $fallbackLocale = $this->getFallbackLocale();

        if (null !== $fallbackLocale && $locale !== $fallbackLocale) {
        }
    }

    /**
     * Check if translation exists.
     *
     * @param string      $message
     * @param null|string $language
     *
     * @return bool
     */
    public function has($message, $language = null)
    {
        if (null === $language) {
            $language = $this->getLanguage();
        }

        $this->assertValidLocale($language);

        $found = 'TODO';

        if ($found) {
            return null !== $found;
        }

        return flase;
    }

    /**
     * Exists record.
     *
     * @param string|array $keys chain keys
     *
     * @return bool|null
     */
    public function exists($keys)
    {
    }

    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    abstract protected function assertValidLocale($locale);

    /**
     * Returns the pluralization instance.
     *
     * @return \Brainwave\Translator\PluralizationRules
     */
    abstract public function getPluralization();
}
