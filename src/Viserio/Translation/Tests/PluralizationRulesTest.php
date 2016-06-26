<?php
namespace Viserio\Translation\Tests;

use ReflectionMethod;
use Viserio\Translation\PluralCategorys\{
    Arabic,
    Balkan,
    Breton,
    Colognian,
    Czech,
    French,
    Gaelic,
    Hebrew,
    Irish,
    Langi,
    Latvian,
    Lithuanian,
    Macedonian,
    Maltese,
    Manx,
    None,
    One,
    Polish,
    Romanian,
    Slovenian,
    Tachelhit,
    Tamazight,
    Two,
    Welsh,
    Zero
};
use Viserio\Translation\PluralizationRules;

class PluralizationRulesTest extends \PHPUnit_Framework_TestCase
{
    protected $createRules;

    protected $object;

    public function setUp()
    {
        $this->object = new PluralizationRules();

        $createRules  = new ReflectionMethod($this->object, 'createRules');
        $createRules->setAccessible(true);

        $this->createRules = $createRules;
    }

    /**
     * @dataProvider provideCreateRules
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
                One::class,
            ],
            [
                ['cs', 'sk'],
                Czech::class,
            ],
            [
                ['ff', 'fr', 'kab'],
                French::class,
            ],
            [
                ['hr', 'ru', 'sr', 'uk', 'be', 'bs', 'sh'],
                Balkan::class,
            ],
            [
                ['lv'],
                Latvian::class,
            ],
            [
                ['lt'],
                Lithuanian::class,
            ],
            [
                ['pl'],
                Polish::class,
            ],
            [
                ['ro', 'mo'],
                Romanian::class,
            ],
            [
                ['sl'],
                Slovenian::class,
            ],
            [
                ['ar'],
                Arabic::class,
            ],
            [
                ['mk'],
                Macedonian::class,
            ],
            [
                ['cy'],
                Welsh::class,
            ],
            [
                ['br'],
                Breton::class,
            ],
            [
                ['lag'],
                Langi::class,
            ],
            [
                ['shi'],
                Tachelhit::class,
            ],
            [
                ['mt'],
                Maltese::class,
            ],
            [
                ['he'],
                Hebrew::class,
            ],
            [
                ['ga'],
                Irish::class,
            ],
            [
                ['gd'],
                Gaelic::class,
            ],
            [
                ['gv'],
                Manx::class,
            ],
            [
                ['tzm'],
                Tamazight::class,
            ],
            [
                ['ksh'],
                Colognian::class,
            ],
            [
                ['se', 'sma', 'smi', 'smj', 'smn', 'sms'],
                Two::class,
            ],
            [
                ['ak', 'am', 'bh', 'fil', 'tl', 'guw', 'hi', 'ln', 'mg', 'nso', 'ti', 'wa'],
                Zero::class,
            ],
            [
                [
                    'az', 'bm', 'fa', 'ig', 'hu', 'ja', 'kde', 'kea', 'ko', 'my', 'ses', 'sg', 'to',
                    'tr', 'vi', 'wo', 'yo', 'zh', 'bo', 'dz', 'id', 'jv', 'ka', 'km', 'kn', 'ms', 'th',
                ],
                None::class,
            ],
        ];
    }

    /**
     * @dataProvider successLangcodes
     */
    public function testLangcodes($nplural, $langCodes)
    {
        $matrix = $this->generateTestData($nplural, $langCodes);
        $this->validateMatrix($nplural, $matrix);
    }
}
