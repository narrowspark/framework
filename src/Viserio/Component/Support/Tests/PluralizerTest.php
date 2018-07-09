<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Pluralizer;
use Viserio\Component\Support\Str;

/**
 * @internal
 */
final class PluralizerTest extends TestCase
{
    public function testGetUncountable(): void
    {
        static::assertInternalType('array', Pluralizer::getUncountable());
    }

    public function testBasicSingular(): void
    {
        static::assertEquals('child', Str::singular('children'));
    }

    public function testBasicPlural(): void
    {
        static::assertEquals('audio', Str::plural('audio', 1));
        static::assertEquals('children', Str::plural('child'));
    }

    public function testCaseSensitiveSingularUsage(): void
    {
        static::assertEquals('Child', Str::singular('Children'));
        static::assertEquals('CHILD', Str::singular('CHILDREN'));
        static::assertEquals('Test', Str::singular('Tests'));
    }

    public function testCaseSensitiveSingularPlural(): void
    {
        static::assertEquals('Children', Str::plural('Child'));
        static::assertEquals('CHILDREN', Str::plural('CHILD'));
        static::assertEquals('Tests', Str::plural('Test'));
    }

    public function testIfEndOfWordPlural(): void
    {
        static::assertEquals('VortexFields', Str::plural('VortexField'));
        static::assertEquals('MatrixFields', Str::plural('MatrixField'));
        static::assertEquals('IndexFields', Str::plural('IndexField'));
        static::assertEquals('VertexFields', Str::plural('VertexField'));
    }
}
