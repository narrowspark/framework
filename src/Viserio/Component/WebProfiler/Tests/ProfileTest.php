<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\Component\WebProfiler\Profile;

class ProfileTest extends TestCase
{
    public function testSetAndGetToken()
    {
        $profile = new Profile('d78a9fa');

        static::assertSame('d78a9fa', $profile->getToken());

        $profile->setToken('4dasda5sd');

        static::assertSame('4dasda5sd', $profile->getToken());
    }

    public function testSetAndGetIp()
    {
        $profile = new Profile('dd5ad');

        $profile->setIp('127.0.0.1');

        static::assertSame('127.0.0.1', $profile->getIp());
    }

    public function testSetAndGetMethod()
    {
        $profile = new Profile('5da7da');

        $profile->setMethod('GET');

        static::assertSame('GET', $profile->getMethod());
    }

    public function testSetAndGetUrl()
    {
        $profile = new Profile('dsa8da');

        $profile->setUrl('/');

        static::assertSame('/', $profile->getUrl());
    }

    public function testSetAndGetTime()
    {
        $profile = new Profile('5d7asd57as2');

        static::assertSame('0', $profile->getTime());

        $profile = new Profile('a7das6d');

        $profile->setTime(12115.13);

        static::assertSame('12115.13', $profile->getTime());
    }

    public function testGetAndSetDate()
    {
        $profile = new Profile('dad65');

        $profile->setDate('12/12/2012');

        static::assertSame('12/12/2012', $profile->getDate());
    }

    public function testSetAndGetStatus()
    {
        $profile = new Profile('da56sd6a');

        $profile->setStatusCode(500);

        static::assertSame('500', $profile->getStatusCode());
    }

    public function testSetGetHasAllCollectors()
    {
        $profile = new Profile('d5adas96');

        $collector = new PhpInfoDataCollector();

        $profile->setCollectors([
            $collector,
        ]);

        static::assertInstanceof(PhpInfoDataCollector::class, $profile->getCollector('php-info-data-collector'));
        static::assertTrue($profile->hasCollector('php-info-data-collector'));
        static::assertEquals(
            [
                'php-info-data-collector' => new PhpInfoDataCollector(),
            ],
            $profile->getCollectors()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Collector "dont" does not exist.
     */
    public function testGetCollectorTothrowException()
    {
        $profile = new Profile('d5adas96');
        $profile->getCollector('dont');
    }
}
