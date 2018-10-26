<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Str;

/**
 * @internal
 */
final class PluralizerTest extends TestCase
{
    public function testBasicSingular(): void
    {
        $this->assertEquals('child', Str::singular('children'));
    }

    public function testBasicPlural(): void
    {
        $this->assertEquals('audio', Str::plural('audio', 1));
        $this->assertEquals('children', Str::plural('child'));
    }

    public function testCaseSensitiveSingularUsage(): void
    {
        $this->assertEquals('Child', Str::singular('Children'));
        $this->assertEquals('CHILD', Str::singular('CHILDREN'));
        $this->assertEquals('Test', Str::singular('Tests'));
    }

    public function testCaseSensitiveSingularPlural(): void
    {
        $this->assertEquals('Children', Str::plural('Child'));
        $this->assertEquals('CHILDREN', Str::plural('CHILD'));
        $this->assertEquals('Tests', Str::plural('Test'));
    }

    public function testIfEndOfWordPlural(): void
    {
        $this->assertEquals('VortexFields', Str::plural('VortexField'));
        $this->assertEquals('MatrixFields', Str::plural('MatrixField'));
        $this->assertEquals('IndexFields', Str::plural('IndexField'));
        $this->assertEquals('VertexFields', Str::plural('VertexField'));
    }
}
