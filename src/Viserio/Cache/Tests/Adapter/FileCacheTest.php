<?php
namespace Viserio\Cache\Tests\Adapter;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Viserio\Cache\Adapter\FileCache;
use Viserio\Contracts\Filesystem\FileNotFoundException;

/**
 * FileCacheTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testNullIsReturnedIfFileDoesntExist()
    {
        $files = $this->mockFilesystem();
        $files->expects($this->once())->method('get')->will($this->throwException(new FileNotFoundException()));

        $store = new FileCache($files, __DIR__);
        $value = $store->get('foo');
        $this->assertNull($value);
    }

    public function testPutCreatesMissingDirectories()
    {
        $files = $this->mockFilesystem();
        $md5 = md5('foo');
        $full_dir = __DIR__.'/'.substr($md5, 0, 2).'/'.substr($md5, 2, 2);
        $files->expects($this->once())->method('makeDirectory')->with($this->equalTo($full_dir), $this->equalTo(0777), $this->equalTo(true));
        $files->expects($this->once())->method('put')->with($this->equalTo($full_dir.'/'.$md5));

        $store = new FileCache($files, __DIR__);
        $store->put('foo', '0000000000', 0);
    }

    public function testExpiredItemsReturnNull()
    {
        $files = $this->mockFilesystem();
        $contents = '0000000000';
        $files->expects($this->once())->method('get')->will($this->returnValue($contents));
        $store = $this->getMock('Viserio\Cache\Adapter\FileCache', ['forget'], [$files, __DIR__]);
        $store->expects($this->once())->method('forget');
        $value = $store->get('foo');
        $this->assertNull($value);
    }

    public function testValidItemReturnsContents()
    {
        $files = $this->mockFilesystem();
        $contents = '9999999999'.serialize('Hello World');
        $files->expects($this->once())->method('get')->will($this->returnValue($contents));

        $store = new FileCache($files, __DIR__);
        $this->assertEquals('Hello World', $store->get('foo'));
    }

    public function testStoreItemProperlyStoresValues()
    {
        $files = $this->mockFilesystem();
        $store = $this->getMock('Viserio\Cache\Adapter\FileCache', ['expiration'], [$files, __DIR__]);
        $store->expects($this->once())->method('expiration')->with($this->equalTo(10))->will($this->returnValue(1111111111));
        $contents = '1111111111'.serialize('Hello World');
        $md5 = md5('foo');
        $cache_dir = substr($md5, 0, 2).'/'.substr($md5, 2, 2);
        $files->expects($this->once())->method('put')->with($this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5), $this->equalTo($contents));
        $store->put('foo', 'Hello World', 10);
    }

    public function testForeversAreStoredWithHighTimestamp()
    {
        $files = $this->mockFilesystem();
        $contents = '9999999999'.serialize('Hello World');
        $md5 = md5('foo');
        $cache_dir = substr($md5, 0, 2).'/'.substr($md5, 2, 2);
        $files->expects($this->once())->method('put')->with($this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5), $this->equalTo($contents));

        $store = new FileCache($files, __DIR__);
        $store->forever('foo', 'Hello World', 10);
    }

    public function testRemoveDeletesFileDoesntExist()
    {
        $files = $this->mockFilesystem();
        $md5 = md5('foobull');
        $cache_dir = substr($md5, 0, 2).'/'.substr($md5, 2, 2);
        $files->expects($this->once())->method('exists')->with($this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5))->will($this->returnValue(false));

        $store = new FileCache($files, __DIR__);
        $store->forget('foobull');
    }

    public function testRemoveDeletesFile()
    {
        $files = $this->mockFilesystem();
        $md5 = md5('foobar');
        $cache_dir = substr($md5, 0, 2).'/'.substr($md5, 2, 2);

        $store = new FileCache($files, __DIR__);
        $store->put('foobar', 'Hello Baby', 10);
        $files->expects($this->once())->method('exists')->with($this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5))->will($this->returnValue(true));
        $files->expects($this->once())->method('delete')->with($this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5));
        $store->forget('foobar');
    }

    public function testFlushCleansDirectory()
    {
        $files = $this->mockFilesystem();
        $files->expects($this->once())->method('isDirectory')->with($this->equalTo(__DIR__))->will($this->returnValue(true));
        $files->expects($this->once())->method('directories')->with($this->equalTo(__DIR__))->will($this->returnValue(['foo']));
        $files->expects($this->once())->method('deleteDirectory')->with($this->equalTo('foo'));

        $store = new FileCache($files, __DIR__);
        $store->flush();
    }

    public function testFlushIgnoreNonExistingDirectory()
    {
        $files = $this->mockFilesystem();
        $files->expects($this->once())->method('isDirectory')->with($this->equalTo(__DIR__.'--wrong'))->will($this->returnValue(false));

        $store = new FileCache($files, __DIR__.'--wrong');
        $store->flush();
    }

    protected function mockFilesystem()
    {
        return $this->getMock('Viserio\Filesystem\Filesystem');
    }
}
