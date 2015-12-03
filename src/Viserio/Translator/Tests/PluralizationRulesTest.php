<?php
namespace Viserio\Translator\Tests;

use Viserio\Translator\PluralizationRules;

class PluralizationRulesTest extends \PHPUnit_Framework_TestCase
{
    protected $createRules;
    protected $object;

    /**
     * @dataProvider  provideCreateRules
     */
    public function testCreateRules($lang, $expected)
    {
        $actual = $this->createRules->invoke($this->object, $lang);
        $this->assertInstanceOf($expected, $actual);
    }

    public function provideCreateRules()
    {
        $provide = [];
        $locales = $this->provideLocales();
        foreach ($locales as $parameters) {
            foreach ($parameters[0] as $lang) {
                $provide[] = [$lang, $parameters[1]];
            }
        }

        return $provide;
    }

    public function provideLocales()
    {
        return [
            [
                [
                    'bem', 'brx', 'da', 'de', 'el', 'en', 'eo', 'es', 'et', 'fi', 'fo', 'gl', 'it', 'nb',
                    'nl', 'nn', 'no', 'sv', 'af', 'bg', 'bn', 'ca', 'eu', 'fur', 'fy', 'gu', 'ha', 'is', 'ku',
                    'lb', 'ml', 'mr', 'nah', 'ne', 'om', 'or', 'pa', 'pap', 'ps', 'so', 'sq', 'sw', 'ta', 'te',
                    'tk', 'ur', 'zu', 'mn', 'gsw', 'chr', 'rm', 'pt',
                ],
                '\Viserio\Translator\PluralCategorys\One',
            ],
            [
                ['cs', 'sk'],
                '\Viserio\Translator\PluralCategorys\Czech',
            ],
            [
                ['ff', 'fr', 'kab'],
                '\Viserio\Translator\PluralCategorys\French',
            ],
            [
                ['hr', 'ru', 'sr', 'uk', 'be', 'bs', 'sh'],
                '\Viserio\Translator\PluralCategorys\Balkan',
            ],
            [
                ['lv'],
                '\Viserio\Translator\PluralCategorys\Latvian',
            ],
            [
                ['lt'],
                '\Viserio\Translator\PluralCategorys\Lithuanian',
            ],
            [
                ['pl'],
                '\Viserio\Translator\PluralCategorys\Polish',
            ],
            [
                ['ro', 'mo'],
                '\Viserio\Translator\PluralCategorys\Romanian',
            ],
            [
                ['sl'],
                '\Viserio\Translator\PluralCategorys\Slovenian',
            ],
            [
                ['ar'],
                '\Viserio\Translator\PluralCategorys\Arabic',
            ],
            [
                ['mk'],
                '\Viserio\Translator\PluralCategorys\Macedonian',
            ],
            [
                ['cy'],
                '\Viserio\Translator\PluralCategorys\Welsh',
            ],
            [
                ['br'],
                '\Viserio\Translator\PluralCategorys\Breton',
            ],
            [
                ['lag'],
                '\Viserio\Translator\PluralCategorys\Langi',
            ],
            [
                ['shi'],
                '\Viserio\Translator\PluralCategorys\Tachelhit',
            ],
            [
                ['mt'],
                '\Viserio\Translator\PluralCategorys\Maltese',
            ],
            [
                ['he'],
                '\Viserio\Translator\PluralCategorys\Hebrew',
            ],
            [
                ['ga'],
                '\Viserio\Translator\PluralCategorys\Irish',
            ],
            [
                ['gd'],
                '\Viserio\Translator\PluralCategorys\Gaelic',
            ],
            [
                ['gv'],
                '\Viserio\Translator\PluralCategorys\Manx',
            ],
            [
                ['tzm'],
                '\Viserio\Translator\PluralCategorys\Tamazight',
            ],
            [
                ['ksh'],
                '\Viserio\Translator\PluralCategorys\Colognian',
            ],
            [
                ['se', 'sma', 'smi', 'smj', 'smn', 'sms'],
                '\Viserio\Translator\PluralCategorys\Two',
            ],
            [
                ['ak', 'am', 'bh', 'fil', 'tl', 'guw', 'hi', 'ln', 'mg', 'nso', 'ti', 'wa'],
                '\Viserio\Translator\PluralCategorys\Zero',
            ],
            [
                [
                    'az', 'bm', 'fa', 'ig', 'hu', 'ja', 'kde', 'kea', 'ko', 'my', 'ses', 'sg', 'to',
                    'tr', 'vi', 'wo', 'yo', 'zh', 'bo', 'dz', 'id', 'jv', 'ka', 'km', 'kn', 'ms', 'th',
                ],
                '\Viserio\Translator\PluralCategorys\None',
            ],
        ];
    }

    /**
     * @dataProvider  provideInvalidPluralRules
     */
    public function testInvalidInstance($lang)
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->createRules->invoke($this->object, $lang);
    }

    public function provideInvalidPluralRules()
    {
        return [
            ['xx'],
            [null],
            [true],
            [false],
            [0],
            [100],
            [-3.14],
        ];
    }

    public function setUp()
    {
        parent::setUp();
        $this->object = new PluralizationRules();
        $this->createRules = new \ReflectionMethod($this->object, 'createRules');
        $this->createRules->setAccessible(true);
    }
}
