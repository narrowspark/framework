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
     * @dataProvider provideInvalidPluralRules
     * @expectedException InvalidArgumentException
     */
    public function testInvalidInstance($lang)
    {
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

    /**
     * @dataProvider successLangcodes
     */
    public function testLangcodes($nplural, $langCodes)
    {
        $matrix = $this->generateTestData($nplural, $langCodes);
        $this->validateMatrix($nplural, $matrix);
    }

    /**
     * This array should contain all currently known langcodes.
     *
     * As it is impossible to have this ever complete we should try as hard as possible to have it almost complete.
     *
     * @return array
     */
    public function successLangcodes()
    {
        return array(
            array('1', array('ay', 'bo', 'cgg', 'dz', 'id', 'ja', 'jbo', 'ka', 'kk', 'km', 'ko', 'ky', 'fa')),
            array('2', array('nl', 'fr', 'en', 'de', 'de_GE', 'hy', 'hy_AM', 'jbo')),
            array('3', array('be', 'bs', 'cs', 'hr', 'cbs')),
            array('4', array('cy', 'mt', 'sl', 'gd', 'kw')),
            array('5', array('ga')),
            array('6', array('ar')),
        );
    }

    /**
     * We validate only on the plural coverage. Thus the real rules is not tested.
     *
     * @param string $nplural       plural expected
     * @param array  $matrix        containing langcodes and their plural index values.
     * @param bool   $expectSuccess
     */
    protected function validateMatrix(string $nplural, array $matrix, bool $expectSuccess = true)
    {
        foreach ($matrix as $langCode => $data) {
            $indexes = array_flip($data);

            if ($expectSuccess) {
                $this->assertEquals($nplural, count($indexes), "Langcode '$langCode' has '$nplural' plural forms.");
            } else {
                $this->assertNotEquals((int) $nplural, count($indexes), "Langcode '$langCode' has '$nplural' plural forms.");
            }
        }
    }

    protected function generateTestData($plural, $langCodes)
    {
        $matrix = [];

        foreach ($langCodes as $langCode) {
            for ($count = 0; $count < 200; ++$count) {
                $plural = $this->object->get($count, $langCode);
                $matrix[$langCode][$count] = $plural;
            }
        }

        return $matrix;
    }
}
