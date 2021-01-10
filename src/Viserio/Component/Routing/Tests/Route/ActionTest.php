<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests\Route;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route\Action;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ActionTest extends TestCase
{
    public function testParserMissingAction(): void
    {
        $this->expectException(\Viserio\Contract\Routing\Exception\LogicException::class);
        $this->expectExceptionMessage('Route for [/] has no action.');

        $parser = Action::parse('/', null);

        $parser['uses']();
    }

    public function testParserWithAction(): void
    {
        $parser = Action::parse('/', static function () {
            return true;
        });

        self::assertTrue($parser['uses']());
    }

    public function testParserFindAction(): void
    {
        $parser = Action::parse('/', ['bar' => 'foo', static function () {
            return true;
        }]);

        self::assertTrue($parser['uses']());
    }

    public function testParserNoInvokeFound(): void
    {
        $this->expectException(\Viserio\Contract\Routing\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid route action: [foo].');

        Action::parse('/', ['uses' => 'foo']);
    }

    public function testParserWithInvoke(): void
    {
        $parser = Action::parse('/', ['uses' => InvokableActionFixture::class]);

        self::assertSame(InvokableActionFixture::class . '@__invoke', $parser['uses']);
    }
}
