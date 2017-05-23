<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Formatters;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Formatters\IntlMessageFormatter;

class IntlMessageFormatterTest extends TestCase
{
    public function setUp()
    {
        if (! extension_loaded('intl')) {
            self::markTestSkipped('The Intl extension is not available.');
        }
    }

    /**
     * @dataProvider provideDataForFormat
     *
     * @param mixed $expected
     * @param mixed $message
     * @param mixed $arguments
     */
    public function testFormat($expected, $message, $arguments)
    {
        $this->assertEquals($expected, trim((new IntlMessageFormatter())->format($message, 'en', $arguments)));
    }

    public function testFormatWithNamedArguments()
    {
        if (version_compare(INTL_ICU_VERSION, '4.8', '<')) {
            $this->markTestSkipped('Format with named arguments can only be run with ICU 4.8 or higher and PHP >= 5.5');
        }

        $chooseMessage = <<<'_MSG_'
{gender_of_host, select,
  female {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to her party.}
      =2 {{host} invites {guest} and one other person to her party.}
     other {{host} invites {guest} as one of the # people invited to her party.}}}
  male   {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to his party.}
      =2 {{host} invites {guest} and one other person to his party.}
     other {{host} invites {guest} as one of the # people invited to his party.}}}
  other {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to their party.}
      =2 {{host} invites {guest} and one other person to their party.}
     other {{host} invites {guest} as one of the # people invited to their party.}}}}
_MSG_;
        $formatter = $this->getMessageFormatter();
        $message   = $formatter->format($chooseMessage, 'en', [
            'gender_of_host' => 'male',
            'num_guests'     => 10,
            'host'           => 'Fabien',
            'guest'          => 'Guilherme',
        ]);
        $this->assertEquals('Fabien invites Guilherme as one of the 9 people invited to his party.', $message);
    }

    public function provideDataForFormat()
    {
        return [
            [
                'There is one apple',
                'There is one apple',
                [],
            ],
            [
                '4,560 monkeys on 123 trees make 37.073 monkeys per tree',
                '{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree',
                [4560, 123, 4560 / 123],
            ],
        ];
    }
}
