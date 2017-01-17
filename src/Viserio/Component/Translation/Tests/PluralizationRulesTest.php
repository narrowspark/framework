<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
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
use Viserio\Component\Translation\PluralizationRules;

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
