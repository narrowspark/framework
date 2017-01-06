<?php
declare(strict_types=1);
namespace Viserio\Translation\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
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
use Viserio\Translation\PluralizationRules;

class PluralizationRulesTest extends TestCase
{
    protected $createRules;

    protected $object;

    public function setUp()
    {
        $this->object = new PluralizationRules();

        $createRules = new ReflectionMethod($this->object, 'createRules');
        $createRules->setAccessible(true);

        $this->createRules = $createRules;
    }

    /**
     * @dataProvider provideCreateRules
     *
     * @param mixed $lang
     * @param mixed $expected
     */
    public function testCreateRules($lang, $expected)
    {
        $actual = $this->createRules->invoke($this->object, $lang);

        self::assertInstanceOf($expected, $actual);
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
}
