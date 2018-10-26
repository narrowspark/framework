<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Route;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route\Action;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;

/**
 * @internal
 */
final class ActionTest extends TestCase
{
    public function testParserMissingAction(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\LogicException::class);
        $this->expectExceptionMessage('Route for [/] has no action.');

        $parser = Action::parse('/', null);

        $parser['uses']();
    }

    public function testParserWithAction(): void
    {
        $parser = Action::parse('/', function () {
            return true;
        });

        $this->assertTrue($parser['uses']());
    }

    public function testParserFindAction(): void
    {
        $parser = Action::parse('/', ['bar' => 'foo', function () {
            return true;
        }]);

        $this->assertTrue($parser['uses']());
    }

    public function testParserNoInvokeFound(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid route action: [foo].');

        Action::parse('/', ['uses' => 'foo']);
    }

    public function testParserWithInvoke(): void
    {
        $parser = Action::parse('/', ['uses' => InvokableActionFixture::class]);

        $this->assertSame(InvokableActionFixture::class . '@__invoke', $parser['uses']);
    }
}
