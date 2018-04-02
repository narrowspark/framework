<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Discovery\Path;

class PathTest extends TestCase
{
    /**
     * @var \Viserio\Component\Discovery\Path
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = new Path(__DIR__);
    }

    public function testGetWorkingDir(): void
    {
        self::assertSame(__DIR__, $this->path->getWorkingDir());
    }

    public function testRelativize(): void
    {
        self::assertSame(
            './',
            $this->path->relativize(__DIR__)
        );
    }

    public function testConcatenateOnWindows(): void
    {
        self::assertEquals(
            'c:\\my-project/src/kernel.php',
            $this->path->concatenate(['c:\\my-project', 'src/', 'kernel.php'])
        );
    }
}
