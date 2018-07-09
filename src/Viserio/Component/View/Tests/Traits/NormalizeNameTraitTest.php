<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Traits\NormalizeNameTrait;

/**
 * @internal
 */
final class NormalizeNameTraitTest extends TestCase
{
    use NormalizeNameTrait;

    /**
     * @dataProvider getMatchingNames
     *
     * @param mixed $name
     * @param mixed $validated
     */
    public function testNormalizeName($name, $validated): void
    {
        $validatedName = $this->normalizeName($name);

        static::assertSame($validated, $validatedName);
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
