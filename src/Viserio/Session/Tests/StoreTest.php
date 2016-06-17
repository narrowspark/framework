<?php
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use ReflectionClass;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Encryption\Encrypter;
use Viserio\Session\Fingerprint\UserAgentGenerator;
use Viserio\Session\Store;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    private $encrypter;
    private $encryptString;
    private $session;

    public function setUp()
    {
        parent::setUp();

        $reflection = new ReflectionClass(Store::class);
        $this->encrypter = new Encrypter(Key::createNewRandomKey());

        $this->session = $reflection->newInstanceArgs(
            [
                'name',
                $this->mock(SessionHandlerContract::class),
                $this->encrypter,
            ]
        );

        $this->encryptString = $this->encrypter->encrypt(
            json_encode(
                [
                    'foo' => 'bar',
                    'bagged' => ['name' => 'viserio'],
                    '__metadata__' => [
                        'firstTrace' => 0,
                        'lastTrace' => 0,
                        'regenerationTrace' => 1,
                        'requestsCount' => 0,
                        'fingerprint' => 0
                    ]
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            )
        );
    }

    public function testSessionIsLoadedFromHandler()
    {
        $session = $this->session;
        $encryptString = $this->encrypter->encrypt(
            json_encode(
                [
                    'foo' => 'bar',
                    'bagged' => ['name' => 'viserio'],
                    '__metadata__' => [
                        'firstTrace' => 0,
                        'lastTrace' => 0,
                        'regenerationTrace' => 0,
                        'requestsCount' => 0,
                        'fingerprint' => 0
                    ]
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            )
        );
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->andReturn($encryptString);
        $session->setId(self::SESSION_ID);
        $session->open();

        $this->assertEquals('bar', $session->get('foo'));
        $this->assertTrue($session->isStarted());
        $this->assertInstanceOf(EncrypterContract::class, $session->getEncrypter());

        $session->getHandler()
            ->shouldReceive('write')
            ->once();
        $session->getHandler()
            ->shouldReceive('gc')
            ->once()
            ->with(86400);

        $session->save();

        $this->assertFalse($session->isStarted());
    }

    public function testName()
    {
        $session = $this->session;

        $this->assertEquals($session->getName(), 'name');

        $session->setName('foo');

        $this->assertEquals($session->getName(), 'foo');
    }

    public function testSessionMigration()
    {
        $session = $this->session;
        $session->start();

        $oldId = $session->getId();
        $session->getHandler()->shouldReceive('destroy')->never();

        $this->assertTrue($session->migrate());
        $this->assertNotEquals($oldId, $session->getId());

        $session = $this->session;
        $oldId = $session->getId();
        $session->getHandler()->shouldReceive('destroy')->once()->with($oldId);

        $this->assertTrue($session->migrate(true));
        $this->assertNotEquals($oldId, $session->getId());
    }

    public function testCantSetInvalidId()
    {
        $session = $this->session;

        $session->setId('wrong');

        $this->assertNotEquals('wrong', $session->getId());
    }

    public function testReplace()
    {
        $session = $this->session;
        $session->start();

        $session->set('foo', 'bar');
        $session->set('qu', 'ux');
        $session->replace(['foo' => 'baz']);

        $this->assertEquals('baz', $session->get('foo'));
        $this->assertEquals('ux', $session->get('qu'));
    }

    public function testSessionInvalidate()
    {
        $session = $this->session;
        $session->start();
        $session->set('foo', 'bar');

        $oldId = $session->getId();

        $this->assertGreaterThan(0, count($session->all()));

        $session->getHandler()->shouldReceive('destroy')->once()->with($oldId);

        $this->assertTrue($session->invalidate());
        $this->assertFalse($session->has('foo'));
        $this->assertNotEquals($oldId, $session->getId());
        $this->assertCount(0, $session->all());
    }

    public function testCanGetRequestsCount()
    {
        $session = $this->session;

        $this->assertEquals(0, $session->getRequestsCount());

        $session->start();

        $this->assertEquals(1, $session->getRequestsCount());
    }

    public function testStartMethodUnsetsAllValues()
    {
        $session = $this->session;
        $session->set('foo', 'bar');
        $session->set('foo2', 'bar');
        $session->set('foo3', 'bar');

        $session->start();

        $this->assertEquals(0, count($session->all()));
    }

    public function testStartMethodResetsLastTraceAndFirstTrace()
    {
        $session = $this->encryptedSession();
        $session->open();

        $lastTrace = $session->getLastTrace();
        $firstTrace = $session->getLastTrace();

        $session->start();

        $this->assertNotEquals($lastTrace, $session->getLastTrace());
        $this->assertNotEquals($firstTrace, $session->getFirstTrace());
    }

    public function testStartMethodResetsRequestsCount()
    {
        $session = $this->session;
        $session->start();

        $this->assertEquals(1, $session->getRequestsCount());
    }

    public function testStartMethodResetsIdRegenerationTrace()
    {
        $session = $this->encryptedSession();
        $session->open();

        $regenerationTrace = $session->getRegenerationTrace();

        $session->start();

        $this->assertNotEquals($regenerationTrace, $session->getRegenerationTrace());
        $this->assertGreaterThanOrEqual(time() - 1, $session->getRegenerationTrace());
    }

    public function testStartMethodGeneratesFingerprint()
    {
        $session = $this->session;
        $key = Key::createNewRandomKey();

        $oldFingerprint = $session->getFingerprint();

        $session->addFingerprintGenerator(new UserAgentGenerator($key->saveToAsciiSafeString(), 'test'));

        $session->start();

        $this->assertSame('', $oldFingerprint);
        $this->assertEquals(40, strlen($session->getFingerprint()));
        $this->assertNotEquals($oldFingerprint, $session->getFingerprint());
    }

    public function testStartMethodOpensSession()
    {
        $session = $this->session;

        $session->start();

        $this->assertTrue($session->isStarted());
    }

    public function testRemove()
    {
        $session = $this->session;
        $session->set('foo', 'bar');

        $pulled = $session->remove('foo');

        $this->assertFalse($session->has('foo'));
        $this->assertEquals('bar', $pulled);
    }

    public function testClear()
    {
        $session = $this->session;
        $session->set('foo', 'bar');
        $session->clear();

        $this->assertFalse($session->has('foo'));
    }

    public function testSessionIdShouldBeRegeneratedIfIdRequestsLimitReached()
    {
        $session = $this->session;
        $session->setIdRequestsLimit(3);
        $session->setId(self::SESSION_ID);
        $session->getHandler()
            ->shouldReceive('read')
            ->times(4);
        $session->getHandler()
            ->shouldReceive('write')
            ->times(3);
        $session->getHandler()
            ->shouldReceive('gc')
            ->times(3);
        $session->getHandler()
            ->shouldReceive('destroy')
            ->times(1);

        $session->open();
        $this->assertSame(1, $session->getRequestsCount());
        $this->assertSame(self::SESSION_ID, $session->getId());

        $session->save();
        $session->open();

        $this->assertSame(2, $session->getRequestsCount());
        $this->assertSame(self::SESSION_ID, $session->getId());

        $session->save();
        $session->open();

        $this->assertSame(3, $session->getRequestsCount());
        $this->assertSame(self::SESSION_ID, $session->getId());

        $session->save();
        $session->open();

        $this->assertSame(4, $session->getRequestsCount());
        $this->assertNotSame(self::SESSION_ID, $session->getId());
    }

    public function testSetAndGetLiveTime()
    {
        $session = $this->session;
        $session->setLiveTime(60);

        $this->assertSame(60, $session->getLiveTime());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetLiveTimeToThrowRuntimeException()
    {
        $session = $this->session;
        $session->start();
        $session->setLiveTime(60);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetLiveTimeToThrowRuntimeExceptionIfTtlIsSmallThenZero()
    {
        $session = $this->session;
        $session->setLiveTime(0);
    }

    public function testSessionIdShouldBeRegeneratedIfIdTtlLimitReached()
    {
        $encryptString = $this->encrypter->encrypt(
            json_encode(
                [
                    'foo' => 'bar',
                    'bagged' => ['name' => 'viserio'],
                    '__metadata__' => [
                        'firstTrace' => 0,
                        'lastTrace' => 0,
                        'regenerationTrace' => 1,
                        'requestsCount' => 0,
                        'fingerprint' => 0
                    ]
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            )
        );
        $session = $this->session;
        $session->setId(self::SESSION_ID);
        $session->getHandler()
            ->shouldReceive('read')
            ->twice()
            ->andReturn($this->encryptString);
        $session->setIdLiveTime(5);
        $session->getHandler()
            ->shouldReceive('write')
            ->times(1);
        $session->getHandler()
            ->shouldReceive('gc')
            ->times(1);
        $session->getHandler()
            ->shouldReceive('destroy')
            ->times(1);
        $session->open();

        $this->assertSame(1, $session->getRequestsCount());
        $this->assertSame(self::SESSION_ID, $session->getId());

        sleep(10);

        $session->save();
        $session->open();

        $this->assertNotSame(self::SESSION_ID, $session->getId());
    }

    private function encryptedSession()
    {
        $session = $this->session;
        $session->setId(self::SESSION_ID);
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->andReturn($this->encryptString);

        return $session;
    }

    public function testDataFlashing()
    {
        $session = $this->session;
        $session->flash('foo', 'bar');
        $session->flash('bar', 0);

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('bar', $session->get('foo'));
        $this->assertEquals(0, $session->get('bar'));

        $session->ageFlashData();

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('bar', $session->get('foo'));
        $this->assertEquals(0, $session->get('bar'));

        $session->ageFlashData();

        $this->assertFalse($session->has('foo'));
        $this->assertNull($session->get('foo'));
    }

    public function testDataFlashingNow()
    {
        $session = $this->session;
        $session->now('foo', 'bar');
        $session->now('bar', 0);

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('bar', $session->get('foo'));
        $this->assertEquals(0, $session->get('bar'));

        $session->ageFlashData();

        $this->assertFalse($session->has('foo'));
        $this->assertNull($session->get('foo'));
    }

    public function testDataMergeNewFlashes()
    {
        $session = $this->session;
        $session->flash('foo', 'bar');
        $session->set('fu', 'baz');
        $session->set('flash.old', ['qu']);

        $this->assertNotFalse(array_search('foo', $session->get('flash.new')));
        $this->assertFalse(array_search('fu', $session->get('flash.new')));

        $session->keep(['fu', 'qu']);

        $this->assertNotFalse(array_search('foo', $session->get('flash.new')));
        $this->assertNotFalse(array_search('fu', $session->get('flash.new')));
        $this->assertNotFalse(array_search('qu', $session->get('flash.new')));
        $this->assertFalse(array_search('qu', $session->get('flash.old')));
    }

    public function testReflash()
    {
        $session = $this->session;
        $session->flash('foo', 'bar');
        $session->set('flash.old', ['foo']);
        $session->reflash();

        $this->assertNotFalse(array_search('foo', $session->get('flash.new')));
        $this->assertFalse(array_search('foo', $session->get('flash.old')));
    }

    public function testReflashWithNow()
    {
        $session = $this->session;
        $session->now('foo', 'bar');
        $session->reflash();

        $this->assertNotFalse(array_search('foo', $session->get('flash.new')));
        $this->assertFalse(array_search('foo', $session->get('flash.old')));
    }
}
