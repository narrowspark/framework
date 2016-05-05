<?php
namespace Viserio\Filesystem\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Filesystem\FilesystemAdapter;
use Viserio\Filesystem\FilesystemManager;

// class FilesystemManagerTest extends \PHPUnit_Framework_TestCase
// {
//     use MockeryTrait;

//     protected function getManager($arr)
//     {
//         $config = $this->mock('Viserio\Contracts\Config\Manager');
//         $config->shouldReceive('get')->once()->with('filesystems')->andReturn($arr);
//         $config->shouldReceive('get')->once()->with('filesystems.default')->andReturn('');

//         return new FilesystemManager($config);
//     }

//     public function testAwsS3ConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'awss3',
//                 [
//                     'key'     => '',
//                     'secret'  => '',
//                     'version' => '',
//                     'region'  => '',
//                 ]
//             )
//         );
//     }

//     public function testDropboxConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'dropbox',
//                 [
//                     'token' => '',
//                     'app'   => '',
//                 ]
//             )
//         );
//     }

//     public function testFtpConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'ftp',
//                 [
//                     'host'     => '',
//                     'port'     => '',
//                     'username' => '',
//                     'password' => '',
//                 ]
//             )
//         );
//     }

//     public function testGridFSConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         // $this->assertInstanceOf(
//         //     FilesystemAdapter::class,
//         //     $manager->driver(
//         //         'gridfs',
//         //         [
//         //             'server' => ''
//         //         ]
//         //     )
//         // );
//     }

//     public function testLocalConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'local',
//                 [
//                     'driver' => 'local',
//                     'path' => __DIR__,
//                 ]
//             )
//         );
//     }

//     public function testNullConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'null'
//             )
//         );
//     }

//     public function testRackspaceConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'rackspace',
//                 [
//                     'username'  => '',
//                     'endpoint'  => '',
//                     'region'    => '',
//                     'container' => '',
//                 ]
//             )
//         );
//     }

//     public function testSftpConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'sftp',
//                 [
//                     'host'     => '',
//                     'port'     => '',
//                     'username' => '',
//                     'password' => '',
//                 ]
//             )
//         );
//     }

//     public function testVfsConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'vfs'
//             )
//         );
//     }

//     public function testWebDavConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'webdav',
//                 [
//                     'prefix'  => '',
//                     'baseUri' => '',
//                 ]
//             )
//         );
//     }

//     public function testZipConnectorDriver()
//     {
//         $manager = $this->getManager([]);

//         $this->assertInstanceOf(
//             FilesystemAdapter::class,
//             $manager->driver(
//                 'zip',
//                 [
//                     'path'   => '',
//                     'prefix' => '',
//                 ]
//             )
//         );
//     }
// }
