<?php
namespace Viserio\Filesystem\Tests\Parsers;

use Viserio\Filesystem\Parsers\Traits\IsGroupTrait;

class IsGroupTraitTest extends \PHPUnit_Framework_TestCase
{
    use IsGroupTrait;

    public function testIsGroup()
    {
        $data = $this->isGroup('narrowspark', ['test' => 'foo']);

        $this->assertSame(['narrowspark::test' => 'foo'], $data);
    }
}
