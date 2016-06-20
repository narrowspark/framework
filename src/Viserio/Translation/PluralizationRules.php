<?php
namespace Viserio\Translation;

use InvalidArgumentException;
use Viserio\Translation\PluralCategorys\Arabic;
use Viserio\Translation\PluralCategorys\Balkan;
use Viserio\Translation\PluralCategorys\Breton;
use Viserio\Translation\PluralCategorys\Colognian;
use Viserio\Translation\PluralCategorys\Czech;
use Viserio\Translation\PluralCategorys\French;
use Viserio\Translation\PluralCategorys\Gaelic;
use Viserio\Translation\PluralCategorys\Hebrew;
use Viserio\Translation\PluralCategorys\Irish;
use Viserio\Translation\PluralCategorys\Langi;
use Viserio\Translation\PluralCategorys\Latvian;
use Viserio\Translation\PluralCategorys\Lithuanian;
use Viserio\Translation\PluralCategorys\Macedonian;
use Viserio\Translation\PluralCategorys\Maltese;
use Viserio\Translation\PluralCategorys\Manx;
use Viserio\Translation\PluralCategorys\None;
use Viserio\Translation\PluralCategorys\One;
use Viserio\Translation\PluralCategorys\Polish;
use Viserio\Translation\PluralCategorys\Romanian;
use Viserio\Translation\PluralCategorys\Slovenian;
use Viserio\Translation\PluralCategorys\Tachelhit;
use Viserio\Translation\PluralCategorys\Tamazight;
use Viserio\Translation\PluralCategorys\Two;
use Viserio\Translation\PluralCategorys\Welsh;
use Viserio\Translation\PluralCategorys\Zero;

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
     * @param string $language
     * @param int    $count
     *
     * @return mixed
     */
    public function get($count, $language)
    {
        if (strlen($language) > 3) {
            $language = substr($language, 0, -strlen(strrchr($language, '_')));
        }

        if (isset($this->rules[$language])) {
            $return = call_user_func($this->rules[$language], $count);

            if (! is_int($return) || $return < 0) {
                return (new Zero())->category(0);
            }

            return $return;
        }

        return $this->createRules($language)->category($count);
    }

    /**
     * Overrides the default plural rule for a given locale.
     *
<<<<<<< HEAD:src/Viserio/Translation/PluralizationRules.php
     * @param string   $language
     * @param callable $rule
     */
    public function set($language, callable $rule)
=======
     * @param callable $rule
     * @param string   $language
     *
     * @throws \LogicException
     */
    public function set(callable $rule, $language)
>>>>>>> develop:src/Viserio/Translator/PluralizationRules.php
    {
        if (strlen($language) > 3) {
            $language = substr($language, 0, -strlen(strrchr($language, '_')));
        }

        $this->rules[$language] = $rule;
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
     * @return PluralCategorys\Arabic|
     *                                 PluralCategorys\Czech|
     *                                 PluralCategorys\One|
     *                                 PluralCategorys\Polish|
     *                                 PluralCategorys\French|
     *                                 PluralCategorys\Balkan|
     *                                 PluralCategorys\Maltese|
     *                                 PluralCategorys\Manx|
     *                                 PluralCategorys\Slovenian|
     *                                 PluralCategorys\Welsh|
     *                                 PluralCategorys\Tachelhit|
     *                                 PluralCategorys\Tamazight|
     *                                 PluralCategorys\Macedonian|
     *                                 PluralCategorys\Lithuanian|
     *                                 PluralCategorys\Hebrew|
     *                                 PluralCategorys\Gaelic|
     *                                 PluralCategorys\Irish|
     *                                 PluralCategorys\Langi|
     *                                 PluralCategorys\Latvian|
     *                                 PluralCategorys\Breton|
     *                                 PluralCategorys\Colognian|
     *                                 PluralCategorys\Romanian|
     *                                 PluralCategorys\Two|
     *                                 PluralCategorys\Zero|
     *                                 PluralCategorys\None
     */
    protected function createRules($prefix)
    {
        if ($prefix === 'ar') {
            return new Arabic();
        } elseif (in_array($prefix, ['cs', 'sk'], true)) {
            return new Czech();
        } elseif (in_array($prefix, [
            'en', 'ny', 'nr', 'no', 'om', 'os', 'ps', 'pa', 'nn', 'or', 'nl', 'lg', 'lb', 'ky', 'ml', 'mr',
            'ne', 'nd', 'nb', 'pt', 'rm', 'ts', 'tn', 'tk', 'ur', 'vo', 'zu', 'xh', 've', 'te', 'ta', 'sq',
            'so', 'sn', 'ss', 'st', 'sw', 'sv', 'ku', 'mn', 'et', 'eo', 'el', 'eu', 'fi', 'fy', 'fo', 'ee',
            'dv', 'bg', 'af', 'bn', 'ca', 'de', 'da', 'gl', 'es', 'it', 'is', 'ks', 'ha', 'kk', 'kl', 'gu',
            'brx', 'mas', 'teo', 'chr', 'cgg', 'tig', 'wae', 'xog', 'ast', 'vun', 'bem', 'syr', 'bez', 'asa',
            'rof', 'ksb', 'rwk', 'haw', 'pap', 'gsw', 'fur', 'saq', 'seh', 'nyn', 'kcg', 'ssy', 'kaj', 'jmc',
            'nah', 'ckb', ], true)) {
            return new One();
        } elseif ($prefix === 'pl') {
            return new Polish();
        } elseif (in_array($prefix, ['fr', 'ff', 'kab'], true)) {
            return new French();
        } elseif (in_array($prefix, ['ru', 'sr', 'uk', 'sh', 'be', 'hr', 'bs'], true)) {
            return new Balkan();
        } elseif ($prefix === 'mt') {
            return new Maltese();
        } elseif ($prefix === 'gv') {
            return new Manx();
        } elseif ($prefix === 'sl') {
            return new Slovenian();
        } elseif ($prefix === 'cy') {
            return new Welsh();
        } elseif ($prefix === 'shi') {
            return new Tachelhit();
        } elseif ($prefix === 'tzm') {
            return new Tamazight();
        } elseif ($prefix === 'mk') {
            return new Macedonian();
        } elseif ($prefix === 'lt') {
            return new Lithuanian();
        } elseif ($prefix === 'he') {
            return new Hebrew();
        } elseif ($prefix === 'gd') {
            return new Gaelic();
        } elseif ($prefix === 'ga') {
            return new Irish();
        } elseif ($prefix === 'lag') {
            return new Langi();
        } elseif ($prefix === 'lv') {
            return new Latvian();
        } elseif ($prefix === 'br') {
            return new Breton();
        } elseif ($prefix === 'ksh') {
            return new Colognian();
        } elseif (in_array($prefix, ['mo', 'ro'], true)) {
            return new Romanian();
        } elseif (in_array($prefix, [
            'se', 'kw', 'iu', 'smn', 'sms', 'smj', 'sma', 'naq', 'smi', ], true)) {
            return new Two();
        } elseif (in_array($prefix, [
            'hi', 'ln', 'mg', 'ak', 'tl', 'am', 'bh', 'wa', 'ti', 'guw', 'fil', 'nso', ], true)) {
            return new Zero();
        } elseif (in_array($prefix, [
            'my', 'sg', 'ms', 'lo', 'kn', 'ko', 'th', 'to', 'yo', 'zh', 'wo', 'vi', 'tr', 'az', 'km', 'id',
            'ig', 'fa', 'dz', 'bm', 'bo', 'ii', 'hu', 'ka', 'jv', 'ja', 'kde', 'ses', 'sah', 'kea', ], true)) {
            return new None();
        }

        throw new InvalidArgumentException('Unknown language prefix: ' . $prefix . '.');
    }
}
