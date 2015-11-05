<?php
namespace Brainwave\Test\Translator;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.6-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Translator\PluralizationRules;

/**
 * PluralizationRulesTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
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
                '\Brainwave\Translator\PluralCategorys\One',
            ],
            [
                ['cs', 'sk'],
                '\Brainwave\Translator\PluralCategorys\Czech',
            ],
            [
                ['ff', 'fr', 'kab'],
                '\Brainwave\Translator\PluralCategorys\French',
            ],
            [
                ['hr', 'ru', 'sr', 'uk', 'be', 'bs', 'sh'],
                '\Brainwave\Translator\PluralCategorys\Balkan',
            ],
            [
                ['lv'],
                '\Brainwave\Translator\PluralCategorys\Latvian',
            ],
            [
                ['lt'],
                '\Brainwave\Translator\PluralCategorys\Lithuanian',
            ],
            [
                ['pl'],
                '\Brainwave\Translator\PluralCategorys\Polish',
            ],
            [
                ['ro', 'mo'],
                '\Brainwave\Translator\PluralCategorys\Romanian',
            ],
            [
                ['sl'],
                '\Brainwave\Translator\PluralCategorys\Slovenian',
            ],
            [
                ['ar'],
                '\Brainwave\Translator\PluralCategorys\Arabic',
            ],
            [
                ['mk'],
                '\Brainwave\Translator\PluralCategorys\Macedonian',
            ],
            [
                ['cy'],
                '\Brainwave\Translator\PluralCategorys\Welsh',
            ],
            [
                ['br'],
                '\Brainwave\Translator\PluralCategorys\Breton',
            ],
            [
                ['lag'],
                '\Brainwave\Translator\PluralCategorys\Langi',
            ],
            [
                ['shi'],
                '\Brainwave\Translator\PluralCategorys\Tachelhit',
            ],
            [
                ['mt'],
                '\Brainwave\Translator\PluralCategorys\Maltese',
            ],
            [
                ['he'],
                '\Brainwave\Translator\PluralCategorys\Hebrew',
            ],
            [
                ['ga'],
                '\Brainwave\Translator\PluralCategorys\Irish',
            ],
            [
                ['gd'],
                '\Brainwave\Translator\PluralCategorys\Gaelic',
            ],
            [
                ['gv'],
                '\Brainwave\Translator\PluralCategorys\Manx',
            ],
            [
                ['tzm'],
                '\Brainwave\Translator\PluralCategorys\Tamazight',
            ],
            [
                ['ksh'],
                '\Brainwave\Translator\PluralCategorys\Colognian',
            ],
            [
                ['se', 'sma', 'smi', 'smj', 'smn', 'sms'],
                '\Brainwave\Translator\PluralCategorys\Two',
            ],
            [
                ['ak', 'am', 'bh', 'fil', 'tl', 'guw', 'hi', 'ln', 'mg', 'nso', 'ti', 'wa'],
                '\Brainwave\Translator\PluralCategorys\Zero',
            ],
            [
                [
                    'az', 'bm', 'fa', 'ig', 'hu', 'ja', 'kde', 'kea', 'ko', 'my', 'ses', 'sg', 'to',
                    'tr', 'vi', 'wo', 'yo', 'zh', 'bo', 'dz', 'id', 'jv', 'ka', 'km', 'kn', 'ms', 'th',
                ],
                '\Brainwave\Translator\PluralCategorys\None',
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
