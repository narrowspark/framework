<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Discovery\Traits\ExpandTargetDirTrait;

class ExpandTargetDirTraitTest extends TestCase
{
    use ExpandTargetDirTrait;

    public function testItCanIdentifyVarsInTargetDir(): void
    {
        $options = ['foo' => 'bar/'];

        $expandedTargetDir = $this->expandTargetDir($options, '%foo%');

        self::assertSame('bar', $expandedTargetDir);
    }
}
