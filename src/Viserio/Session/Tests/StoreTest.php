<?php
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use ReflectionClass;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
use Viserio\Encryption\Encrypter;
use Viserio\Session\Store;
use Viserio\Session\Fingerprint\UserAgentGenerator;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    private $encrypter;
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

        $session->getHandler()
            ->shouldReceive('write')
            ->once();
        $session->getHandler()
            ->shouldReceive('gc')
            ->once()
            ->with(1440);

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

    public function testStartMethodResetsLastTrace()
    {
        $session = $this->session;
        $lastTrace = $session->getLastTrace();

        $session->start();

        $this->assertNotEquals($lastTrace, $session->getLastTrace());
    }

    public function testStartMethodResetsRequestsCount()
    {
        $session = $this->session;
        $session->start();

        $this->assertEquals(1, $session->getRequestsCount());
    }

    public function testStartMethodResetsIdRegenerationTrace()
    {
        $session = $this->session;
        $regenerationTrace = $session->getRegenerationTrace();

        $session->start();

        $this->assertNotEquals($regenerationTrace, $session->getRegenerationTrace());
        $this->assertGreaterThanOrEqual(time() - 1, $session->getRegenerationTrace());
    }

    public function testStartMethodGeneratesFingerprint()
    {
        $session = $this->session;

        $oldFingerprint = $session->getFingerprint();

        $session->addFingerprintGenerator(new UserAgentGenerator('test'));

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
}
