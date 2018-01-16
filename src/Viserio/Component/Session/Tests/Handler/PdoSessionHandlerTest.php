<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Viserio\Component\Session\Handler\PdoSessionHandler;
use Viserio\Component\Session\Tests\Fixtures\MockPdo;

/**
 * @requires extension pdo_sqlite
 * @group time-sensitive
 */
class PdoSessionHandlerTest extends TestCase
{
    private const TTL = 300;

    /**
     * @var string
     */
    private $dbFile;

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        // make sure the temporary database file is deleted when it has been created (even when a test fails)
        if ($this->dbFile) {
            @unlink($this->dbFile);
        }

        parent::tearDown();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongPdoErrMode(): void
    {
        $pdo = $this->getMemorySqlitePdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        new PdoSessionHandler($pdo, self::TTL);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInexistentTable(): void
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL, ['db_table' => 'inexistent_table']);
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateTableTwice(): void
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL);
        $storage->createTable();
    }

    public function testWithLazyDsnConnection(): void
    {
        $dsn = $this->getPersistentSqliteDsn();

        $storage = new PdoSessionHandler($dsn, self::TTL);
        $storage->createTable();
        $storage->open('', 'sid');
        $data = $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();

        self::assertSame('', $data, 'New session returns empty string data');

        $storage->open('', 'sid');
        $data = $storage->read('id');
        $storage->close();

        self::assertSame('data', $data, 'Written value can be read back correctly');
    }

    public function testWithLazySavePathConnection(): void
    {
        $dsn = $this->getPersistentSqliteDsn();

        // Open is called with what ini_set('session.save_path', $dsn) would mean
        $storage = new PdoSessionHandler(null, self::TTL);
        $storage->open($dsn, 'sid');
        $storage->createTable();
        $data = $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();

        self::assertSame('', $data, 'New session returns empty string data');

        $storage->open($dsn, 'sid');
        $data = $storage->read('id');
        $storage->close();

        self::assertSame('data', $data, 'Written value can be read back correctly');
    }

    public function testReadWriteReadWithNullByte(): void
    {
        $sessionData = 'da' . "\0" . 'ta';

        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL);
        $storage->open('', 'sid');
        $readData = $storage->read('id');
        $storage->write('id', $sessionData);
        $storage->close();

        self::assertSame('', $readData, 'New session returns empty string data');

        $storage->open('', 'sid');
        $readData = $storage->read('id');
        $storage->close();

        self::assertSame($sessionData, $readData, 'Written value can be read back correctly');
    }

    public function testReadConvertsStreamToString(): void
    {
        $pdo                = new MockPdo('pgsql');
        $pdo->prepareResult = $this->getMockBuilder('PDOStatement')->getMock();

        $content = 'foobar';
        $stream  = $this->createStream($content);

        $pdo->prepareResult->expects($this->once())->method('fetchAll')
            ->will($this->returnValue([[$stream, 42, time()]]));

        $storage = new PdoSessionHandler($pdo, self::TTL);
        $result  = $storage->read('foo');

        self::assertSame($content, $result);
    }

    public function testReadLockedConvertsStreamToString(): void
    {
        if (ini_get('session.use_strict_mode')) {
            $this->markTestSkipped('Strict mode needs no locking for new sessions.');
        }

        $pdo        = new MockPdo('pgsql');
        $selectStmt = $this->getMockBuilder('PDOStatement')->getMock();
        $insertStmt = $this->getMockBuilder('PDOStatement')->getMock();

        $pdo->prepareResult = function ($statement) use ($selectStmt, $insertStmt) {
            return 0 === mb_strpos($statement, 'INSERT') ? $insertStmt : $selectStmt;
        };

        $content   = 'foobar';
        $stream    = $this->createStream($content);
        $exception = null;

        $selectStmt->expects($this->atLeast(2))->method('fetchAll')
            ->will($this->returnCallback(function () use (&$exception, $stream) {
                return $exception ? [[$stream, 42, time()]] : [];
            }));

        $insertStmt->expects($this->once())->method('execute')
            ->will($this->returnCallback(function () use (&$exception): void {
                throw $exception = new PDOException('', 23);
            }));

        $storage = new PdoSessionHandler($pdo, self::TTL);
        $result  = $storage->read('foo');

        self::assertSame($content, $result);
    }

    public function testReadingRequiresExactlySameId(): void
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL);
        $storage->open('', 'sid');
        $storage->write('id', 'data');
        $storage->write('test', 'data');
        $storage->write('space ', 'data');
        $storage->close();

        $storage->open('', 'sid');
        $readDataCaseSensitive = $storage->read('ID');
        $readDataNoCharFolding = $storage->read('tést');
        $readDataKeepSpace     = $storage->read('space ');
        $readDataExtraSpace    = $storage->read('space  ');
        $storage->close();

        self::assertSame('', $readDataCaseSensitive, 'Retrieval by ID should be case-sensitive (collation setting)');
        self::assertSame('', $readDataNoCharFolding, 'Retrieval by ID should not do character folding (collation setting)');
        self::assertSame('data', $readDataKeepSpace, 'Retrieval by ID requires spaces as-is');
        self::assertSame('', $readDataExtraSpace, 'Retrieval by ID requires spaces as-is');
    }

    /**
     * Simulates session_regenerate_id(true) which will require an INSERT or UPDATE (replace).
     */
    public function testWriteDifferentSessionIdThanRead(): void
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL);
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->destroy('id');
        $storage->write('new_id', 'data_of_new_session_id');
        $storage->close();

        $storage->open('', 'sid');
        $data = $storage->read('new_id');
        $storage->close();

        self::assertSame('data_of_new_session_id', $data, 'Data of regenerated session id is available');
    }

    public function testWrongUsageStillWorks(): void
    {
        // wrong method sequence that should no happen, but still works
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL);
        $storage->write('id', 'data');
        $storage->write('other_id', 'other_data');
        $storage->destroy('inexistent');
        $storage->open('', 'sid');
        $data      = $storage->read('id');
        $otherData = $storage->read('other_id');
        $storage->close();

        self::assertSame('data', $data);
        self::assertSame('other_data', $otherData);
    }

    public function testSessionDestroy(): void
    {
        $pdo     = $this->getMemorySqlitePdo();
        $storage = new PdoSessionHandler($pdo, self::TTL);

        $storage->open('', 'sid');
        $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();

        self::assertEquals(1, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn());

        $storage->open('', 'sid');
        $storage->read('id');
        $storage->destroy('id');
        $storage->close();

        self::assertEquals(0, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn());

        $storage->open('', 'sid');
        $data = $storage->read('id');
        $storage->close();

        self::assertSame('', $data, 'Destroyed session returns empty string');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionGC(): void
    {
        $pdo     = $this->getMemorySqlitePdo();
        $storage = new PdoSessionHandler($pdo, 1000);

        $storage->open('', 'sid');
        $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();

        $storage->open('', 'sid');
        $storage->read('gc_id');

        self::assertEquals(1, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn(), 'No session pruned because gc not called');

        $storage->open('', 'sid');
        $data = $storage->read('gc_id');
        $storage->gc(-1);
        $storage->close();

        self::assertSame('', $data, 'Session already considered garbage, so not returning data even if it is not pruned yet');
        self::assertEquals(1, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn(), 'Expired session is pruned');
    }

    public function testGetConnection(): void
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), self::TTL);

        $method = new ReflectionMethod($storage, 'getConnection');
        $method->setAccessible(true);

        self::assertInstanceOf(PDO::class, $method->invoke($storage));
    }

    public function testGetConnectionConnectsIfNeeded(): void
    {
        $storage = new PdoSessionHandler('sqlite::memory:', self::TTL);

        $method = new ReflectionMethod($storage, 'getConnection');
        $method->setAccessible(true);

        self::assertInstanceOf(PDO::class, $method->invoke($storage));
    }

    private function getPersistentSqliteDsn()
    {
        $this->dbFile = tempnam(sys_get_temp_dir(), 'sf2_sqlite_sessions');

        return 'sqlite:' . $this->dbFile;
    }

    private function getMemorySqlitePdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $storage = new PdoSessionHandler($pdo, self::TTL);
        $storage->createTable();

        return $pdo;
    }

    private function createStream($content)
    {
        $stream = tmpfile();
        fwrite($stream, $content);
        fseek($stream, 0);

        return $stream;
    }
}
