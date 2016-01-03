<?php
namespace Viserio\Translator\Tests;

use ReflectionMethod;
use Viserio\Translator\PluralizationRules;
use Viserio\Translator\PluralCategorys\Arabic;
use Viserio\Translator\PluralCategorys\Balkan;
use Viserio\Translator\PluralCategorys\Breton;
use Viserio\Translator\PluralCategorys\Colognian;
use Viserio\Translator\PluralCategorys\Czech;
use Viserio\Translator\PluralCategorys\French;
use Viserio\Translator\PluralCategorys\Gaelic;
use Viserio\Translator\PluralCategorys\Hebrew;
use Viserio\Translator\PluralCategorys\Irish;
use Viserio\Translator\PluralCategorys\Langi;
use Viserio\Translator\PluralCategorys\Latvian;
use Viserio\Translator\PluralCategorys\Lithuanian;
use Viserio\Translator\PluralCategorys\Macedonian;
use Viserio\Translator\PluralCategorys\Maltese;
use Viserio\Translator\PluralCategorys\Manx;
use Viserio\Translator\PluralCategorys\None;
use Viserio\Translator\PluralCategorys\One;
use Viserio\Translator\PluralCategorys\Polish;
use Viserio\Translator\PluralCategorys\Romanian;
use Viserio\Translator\PluralCategorys\Slovenian;
use Viserio\Translator\PluralCategorys\Tachelhit;
use Viserio\Translator\PluralCategorys\Tamazight;
use Viserio\Translator\PluralCategorys\Two;
use Viserio\Translator\PluralCategorys\Welsh;
use Viserio\Translator\PluralCategorys\Zero;

class PluralizationRulesTest extends \PHPUnit_Framework_TestCase
{
    protected $createRules;

    protected $object;

    public function setUp()
    {

        $this->object      = new PluralizationRules();

        $createRules = new ReflectionMethod($this->object, 'createRules');
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
}
