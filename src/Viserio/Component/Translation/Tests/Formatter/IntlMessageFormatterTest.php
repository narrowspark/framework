<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Translation\Tests\Formatter;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Contract\Translation\Exception\CannotFormatException;
use Viserio\Contract\Translation\Exception\CannotInstantiateFormatterException;

/**
 * @internal
 *
 * @small
 */
final class IntlMessageFormatterTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (! \extension_loaded('intl')) {
            self::markTestSkipped('The Intl extension is not available.');
        }
    }

    public function testFormatWithEmptyString(): void
    {
        self::assertSame('', (new IntlMessageFormatter())->format('', 'en', []));
    }

    public function testFormatToThrowException(): void
    {
        $this->expectException(CannotInstantiateFormatterException::class);
        $this->expectExceptionMessage('Constructor failed');

        self::assertSame('', (new IntlMessageFormatter())->format('{ gender, select,
\u{a0}\u{a0}male {He avoids bugs}
female {She avoids bugs} }', 'en', [1]));
    }

    public function testFormatToThrowExceptionOnFormat(): void
    {
        $this->expectException(CannotFormatException::class);
        $this->expectExceptionMessage('The argument for key \'catchDate\' cannot be used as a date or time: U_ILLEGAL_ARGUMENT_ERROR');

        (new IntlMessageFormatter())->format('Caught on { catchDate, date, short }', 'en', ['catchDate' => '1/1/1']);
    }

    /**
     * @dataProvider provideFormatCases
     *
     * @param mixed $expected
     * @param mixed $message
     * @param mixed $arguments
     */
    public function testFormat($expected, $message, $arguments): void
    {
        self::assertEquals($expected, \trim((new IntlMessageFormatter())->format($message, 'en', $arguments)));
    }

    public function provideFormatCases(): iterable
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

    public function testFormatWithNamedArguments(): void
    {
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

        $formatter = new IntlMessageFormatter();
        $message = $formatter->format($chooseMessage, 'en', [
            'gender_of_host' => 'male',
            'num_guests' => 10,
            'host' => 'Fabien',
            'guest' => 'Guilherme',
        ]);

        self::assertEquals('Fabien invites Guilherme as one of the 9 people invited to his party.', $message);
    }
}
