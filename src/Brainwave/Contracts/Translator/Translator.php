<?php

namespace Brainwave\Contracts\Translator;

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
 * @version     0.10.0-dev
 */

/**
 * Translator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
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
