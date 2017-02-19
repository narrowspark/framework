<?php
declare(strict_types=1);
namespace Viserio\Component\Translation;

use Viserio\Component\Contracts\Translation\PluralizationRules as PluralizationRulesContract;
use Viserio\Component\Translation\PluralCategorys\Arabic;
use Viserio\Component\Translation\PluralCategorys\Balkan;
use Viserio\Component\Translation\PluralCategorys\Breton;
use Viserio\Component\Translation\PluralCategorys\Colognian;
use Viserio\Component\Translation\PluralCategorys\Czech;
use Viserio\Component\Translation\PluralCategorys\French;
use Viserio\Component\Translation\PluralCategorys\Gaelic;
use Viserio\Component\Translation\PluralCategorys\Hebrew;
use Viserio\Component\Translation\PluralCategorys\Irish;
use Viserio\Component\Translation\PluralCategorys\Langi;
use Viserio\Component\Translation\PluralCategorys\Latvian;
use Viserio\Component\Translation\PluralCategorys\Lithuanian;
use Viserio\Component\Translation\PluralCategorys\Macedonian;
use Viserio\Component\Translation\PluralCategorys\Maltese;
use Viserio\Component\Translation\PluralCategorys\Manx;
use Viserio\Component\Translation\PluralCategorys\None;
use Viserio\Component\Translation\PluralCategorys\One;
use Viserio\Component\Translation\PluralCategorys\Polish;
use Viserio\Component\Translation\PluralCategorys\Romanian;
use Viserio\Component\Translation\PluralCategorys\Slovenian;
use Viserio\Component\Translation\PluralCategorys\Tachelhit;
use Viserio\Component\Translation\PluralCategorys\Tamazight;
use Viserio\Component\Translation\PluralCategorys\Two;
use Viserio\Component\Translation\PluralCategorys\Welsh;
use Viserio\Component\Translation\PluralCategorys\Zero;

class PluralizationRules implements PluralizationRulesContract
{
    /**
     * Rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * {@inheritdoc}
     */
    public function get(int $count, string $language): int
    {
        if (mb_strlen($language) > 3) {
            $language = mb_substr($language, 0, -mb_strlen(mb_strrchr($language, '_')));
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
     * {@inheritdoc}
     */
    public function set(string $language, callable $rule): PluralizationRulesContract
    {
        if (mb_strlen($language) > 3) {
            $language = mb_substr($language, 0, -mb_strlen(mb_strrchr($language, '_')));
        }

        $this->rules[$language] = $rule;

        return $this;
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
     * @return \Viserio\Component\Contracts\Translation\PluralCategory
     */
    protected function createRules(string $prefix)
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

        return new Zero();
    }
}
