<?php
namespace Viserio\Contracts\Translator;

/**
 * Translator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
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
     * @param String $defaultLang A string representing the default language to translate into. (e.g. 'en').
     *
     * @return self
     */
    public function setLocale($defaultLang);
}
