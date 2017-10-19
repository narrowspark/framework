<?php
declare(strict_types=1);
// @todo finish test
//declare(strict_types=1);
//namespace Viserio\Component\Filesystem\Tests\Stream;
//
//use org\bovigo\vfs\vfsStream;
//use PHPUnit\Framework\TestCase;
//use Viserio\Component\Filesystem\Stream\MutableFile;
//
//class MutableFileTest extends TestCase
//{
//    /**
//     * @var \org\bovigo\vfs\vfsStreamDirectory
//     */
//    private $root;
//
//    /**
//     * Setup the environment.
//     */
//    public function setUp(): void
//    {
//        $this->root = vfsStream::setup();
//    }

//    public function testRead()
//    {
//        $filename = vfsStream::newFile('temp.txt')->at($this->root)->url();
//
//        $buf = \random_bytes(65537);
//
//        \file_put_contents($filename, $buf);
//
//        $fStream = new MutableFile($filename);
//    }
//}
