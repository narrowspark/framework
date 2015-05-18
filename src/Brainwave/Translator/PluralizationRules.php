<?php

namespace Brainwave\Translator;

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
 * PluralizationRules.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class PluralizationRules
{
    /**
     * Rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Returns the plural position to use for the given locale and number.
     *
     * @param int|null    $count
     * @param string|null $language
     *
     * @return mixed
     */
    public function get($count = null, $language = null)
    {
        if (null === $count) {
            return '';
        }

        if (strlen($language) > 3) {
            $language = substr($language, 0, -strlen(strrchr($language, '_')));
        }

        if (isset($this->rules[$language])) {
            $return = call_user_func($this->rules[$language], $count);

            if (!is_int($return) || $return < 0) {
                return (new PluralCategorys\Zero())->category(0);
            }

            return $return;
        }

        return $this->getPlural($language)->category($count);
    }

    /**
     * Returns the plural definition to use.
     *
     * The plural rules are derived from code of the Zend Framework (2010-09-25),
     * which is subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     * Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com).
     *
     * @param string $prefix Locale to use
     *
     * @return PluralCategorys\Arabic|PluralCategorys\Czech|PluralCategorys\One|PluralCategorys\Polish|PluralCategorys\French|PluralCategorys\Balkan|PluralCategorys\Maltese|PluralCategorys\Manx|PluralCategorys\Slovenian|PluralCategorys\Welsh|PluralCategorys\Tachelhit|PluralCategorys\Tamazight|PluralCategorys\Macedonian|PluralCategorys\Lithuanian|PluralCategorys\Hebrew|PluralCategorys\Gaelic|PluralCategorys\Irish|PluralCategorys\Langi|PluralCategorys\Latvian|PluralCategorys\Breton|PluralCategorys\Colognian|PluralCategorys\Romanian|PluralCategorys\Two|PluralCategorys\Zero|PluralCategorys\None
     */
    protected function getPlural($prefix)
    {
        if ($prefix === 'ar') {
            return new PluralCategorys\Arabic();
        } elseif (in_array($prefix, ['cs', 'sk'], true)) {
            return new PluralCategorys\Czech();
        } elseif (in_array($prefix, [
            'en', 'ny', 'nr', 'no', 'om', 'os', 'ps', 'pa', 'nn', 'or', 'nl', 'lg', 'lb', 'ky', 'ml', 'mr',
            'ne', 'nd', 'nb', 'pt', 'rm', 'ts', 'tn', 'tk', 'ur', 'vo', 'zu', 'xh', 've', 'te', 'ta', 'sq',
            'so', 'sn', 'ss', 'st', 'sw', 'sv', 'ku', 'mn', 'et', 'eo', 'el', 'eu', 'fi', 'fy', 'fo', 'ee',
            'dv', 'bg', 'af', 'bn', 'ca', 'de', 'da', 'gl', 'es', 'it', 'is', 'ks', 'ha', 'kk', 'kl', 'gu',
            'brx', 'mas', 'teo', 'chr', 'cgg', 'tig', 'wae', 'xog', 'ast', 'vun', 'bem', 'syr', 'bez', 'asa',
            'rof', 'ksb', 'rwk', 'haw', 'pap', 'gsw', 'fur', 'saq', 'seh', 'nyn', 'kcg', 'ssy', 'kaj', 'jmc',
            'nah', 'ckb', ], true)) {
            return new PluralCategorys\One();
        } elseif ($prefix === 'pl') {
            return new PluralCategorys\Polish();
        } elseif (in_array($prefix, ['fr', 'ff', 'kab'], true)) {
            return new PluralCategorys\French();
        } elseif (in_array($prefix, ['ru', 'sr', 'uk', 'sh', 'be', 'hr', 'bs'], true)) {
            return new PluralCategorys\Balkan();
        } elseif ($prefix === 'mt') {
            return new PluralCategorys\Maltese();
        } elseif ($prefix === 'gv') {
            return new PluralCategorys\Manx();
        } elseif ($prefix === 'sl') {
            return new PluralCategorys\Slovenian();
        } elseif ($prefix === 'cy') {
            return new PluralCategorys\Welsh();
        } elseif ($prefix === 'shi') {
            return new PluralCategorys\Tachelhit();
        } elseif ($prefix === 'tzm') {
            return new PluralCategorys\Tamazight();
        } elseif ($prefix === 'mk') {
            return new PluralCategorys\Macedonian();
        } elseif ($prefix === 'lt') {
            return new PluralCategorys\Lithuanian();
        } elseif ($prefix === 'he') {
            return new PluralCategorys\Hebrew();
        } elseif ($prefix === 'gd') {
            return new PluralCategorys\Gaelic();
        } elseif ($prefix === 'ga') {
            return new PluralCategorys\Irish();
        } elseif ($prefix === 'lag') {
            return new PluralCategorys\Langi();
        } elseif ($prefix === 'lv') {
            return new PluralCategorys\Latvian();
        } elseif ($prefix === 'br') {
            return new PluralCategorys\Breton();
        } elseif ($prefix === 'ksh') {
            return new PluralCategorys\Colognian();
        } elseif (in_array($prefix, ['mo', 'ro'], true)) {
            return new PluralCategorys\Romanian();
        } elseif (in_array($prefix, [
            'se', 'kw', 'iu', 'smn', 'sms', 'smj', 'sma', 'naq', 'smi', ], true)) {
            return new PluralCategorys\Two();
        } elseif (in_array($prefix, [
            'hi', 'ln', 'mg', 'ak', 'tl', 'am', 'bh', 'wa', 'ti', 'guw', 'fil', 'nso', ], true)) {
            return new PluralCategorys\Zero();
        } elseif (in_array($prefix, [
            'my', 'sg', 'ms', 'lo', 'kn', 'ko', 'th', 'to', 'yo', 'zh', 'wo', 'vi', 'tr', 'az', 'km', 'id',
            'ig', 'fa', 'dz', 'bm', 'bo', 'ii', 'hu', 'ka', 'jv', 'ja', 'kde', 'ses', 'sah', 'kea', ], true)) {
            return new PluralCategorys\None();
        }

        throw new \InvalidArgumentException('Unknown language prefix: '.$prefix.'.');
    }

    /**
     * Overrides the default plural rule for a given locale.
     *
     * @param callable $rule
     * @param string   $language
     *
     * @throws \LogicException
     */
    public function set(callable $rule, $language)
    {
        if (strlen($language) > 3) {
            $language = substr($language, 0, -strlen(strrchr($language, '_')));
        }

        $this->rules[$language] = $rule;
    }
}
