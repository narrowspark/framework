<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Route;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Route\Action;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;

class ActionTest extends TestCase
{
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Route for [/] has no action.
     */
    public function testParserMissingAction()
    {
        $parser = Action::parse('/', null);

        $parser['uses']();
    }

    public function testParserWithAction()
    {
        $parser = Action::parse('/', function () {
            return true;
        });

        self::assertTrue($parser['uses']());
    }

    public function testParserFindAction()
    {
        $parser = Action::parse('/', ['bar' => 'foo', function () {
            return true;
        }]);

        self::assertTrue($parser['uses']());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Invalid route action: [foo].
     */
    public function testParserNoInvokeFound()
    {
        Action::parse('/', ['uses' => 'foo']);
    }

    public function testParserWithInvoke()
    {
        $parser = Action::parse('/', ['uses' => InvokableActionFixture::class]);

        self::assertSame(InvokableActionFixture::class . '@__invoke', $parser['uses']);
    }
}
