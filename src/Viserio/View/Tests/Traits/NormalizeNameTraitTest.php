<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Traits;

use Viserio\View\Traits\NormalizeNameTrait;

class NormalizeNameTraitTest extends \PHPUnit_Framework_TestCase
{
    use NormalizeNameTrait;

    /**
     * @dataProvider getMatchingNames
     */
    public function testNormalizeName($name, $validated)
    {
        $validatedName = $this->normalizeName($name);

        $this->assertSame($validated, $validatedName);
    }

    public function getMatchingNames()
    {
        return [
            ['test/foo', 'test.foo'],
            ['path::test/foo', 'path::test.foo'],
            ['deep/path::test/foo', 'deep/path::test.foo'],
        ];
    }
}
