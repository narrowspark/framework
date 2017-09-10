<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Route;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route\Action;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;

class ActionTest extends TestCase
{
    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\LogicException
     * @expectedExceptionMessage Route for [/] has no action.
     */
    public function testParserMissingAction(): void
    {
        $parser = Action::parse('/', null);

        $parser['uses']();
    }

    public function testParserWithAction(): void
    {
        $parser = Action::parse('/', function () {
            return true;
        });

        self::assertTrue($parser['uses']());
    }

    public function testParserFindAction(): void
    {
        $parser = Action::parse('/', ['bar' => 'foo', function () {
            return true;
        }]);

        self::assertTrue($parser['uses']());
    }

    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\UnexpectedValueException
     * @expectedExceptionMessage Invalid route action: [foo].
     */
    public function testParserNoInvokeFound(): void
    {
        Action::parse('/', ['uses' => 'foo']);
    }

    public function testParserWithInvoke(): void
    {
        $parser = Action::parse('/', ['uses' => InvokableActionFixture::class]);

        self::assertSame(InvokableActionFixture::class . '@__invoke', $parser['uses']);
    }
}
