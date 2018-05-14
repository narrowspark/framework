<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use PDO;
use PDOException;
use Viserio\Component\Contract\Session\Exception\DomainException;
use Viserio\Component\Contract\Session\Exception\InvalidArgumentException;

/**
 * Session handler using a PDO connection to read and write data.
 *
 * It works with MySQL, PostgreSQL, Oracle, SQL Server and SQLite and implements
 * different locking strategies to handle concurrent access to the same session.
 * Locking is necessary to prevent loss of data due to race conditions and to keep
 * the session data consistent between read() and write(). With locking, requests
 * for the same session will wait until the other one finished writing. For this
 * reason it's best practice to close a session as early as possible to improve
 * concurrency. PHPs internal files session handler also implements locking.
 *
 * Attention: Since SQLite does not support row level locks but locks the whole database,
 * it means only one session can be accessed at a time. Even different sessions would wait
 * for another to finish. So saving session in SQLite should only be considered for
 * development or prototypes.
 *
 * Session data is a binary string that can contain non-printable characters like the null byte.
 * For this reason it must be saved in a binary column in the database like BLOB in MySQL.
 * Saving it in a character column could corrupt the data. You can use createTable()
 * to initialize a correctly defined table.
 *
 * @see http://php.net/sessionhandlerinterface
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Michael Williams <michael.williams@funsational.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class PdoSessionHandler extends AbstractSessionHandler
{
    /**
     * No locking is done. This means sessions are prone to loss of data due to
     * race conditions of concurrent requests to the same session. The last session
     * write will win in this case. It might be useful when you implement your own
     * logic to deal with this like an optimistic approach.
     */
    public const LOCK_NONE = 0;

    /**
     * Creates an application-level lock on a session. The disadvantage is that the
     * lock is not enforced by the database and thus other, unaware parts of the
     * application could still concurrently modify the session. The advantage is it
     * does not require a transaction.
     * This mode is not available for SQLite and not yet implemented for oci and sqlsrv.
     */
    public const LOCK_ADVISORY = 1;

    /**
     * Issues a real row lock. Since it uses a transaction between opening and
     * closing a session, you have to be careful when you use same database connection
     * that you also use for your application logic. This mode is the default because
     * it's the only reliable solution across DBMSs.
     */
    public const LOCK_TRANSACTIONAL = 2;

    /**
     * PDO instance or null when not connected yet.
     *
     * @var null|\PDO
     */
    private $pdo;

    /**
     * DSN string or false when lazy connection disabled.
     *
     * @var null|false|string
     */
    private $dsn = false;

    /**
     * Database driver.
     *
     * @var string
     */
    private $driver;

    /**
     * Table name.
     *
     * @var string
     */
    private $table = 'sessions';

    /**
     * Column for session id.
     *
     * @var string
     */
    private $idCol = 'sess_id';

    /**
     * Column for session data.
     *
     * @var string
     */
    private $dataCol = 'sess_data';

    /**
     * Column for lifetime.
     *
     * @var string
     */
    private $lifetimeCol = 'sess_lifetime';

    /**
     * Column for timestamp.
     *
     * @var string
     */
    private $timeCol = 'sess_time';

    /**
     * Username when lazy-connect.
     *
     * @var string
     */
    private $username = '';

    /**
     * Password when lazy-connect.
     *
     * @var string
     */
    private $password = '';

    /**
     * Connection options when lazy-connect.
     *
     * @var array
     */
    private $connectionOptions = [];

    /**
     * The strategy for locking, see constants.
     *
     * @var int
     */
    private $lockMode = self::LOCK_TRANSACTIONAL;

    /**
     * It's an array to support multiple reads before closing which is manual, non-standard usage.
     *
     * @var \PDOStatement[] An array of statements to release advisory locks
     */
    private $unlockStatements = [];

    /**
     * True when the current session exists but expired according lifetime.
     *
     * @var bool
     */
    private $sessionExpired = false;

    /**
     * Whether a transaction is active.
     *
     * @var bool
     */
    private $inTransaction = false;

    /**
     * Whether gc() has been called.
     *
     * @var bool
     */
    private $gcCalled = false;

    /**
     * The number of seconds the session should be valid.
     *
     * @var int
     */
    private $lifetime;

    /**
     * You can either pass an existing database connection as PDO instance or
     * pass a DSN string that will be used to lazy-connect to the database
     * when the session is actually used.
     *
     * List of available options:
     *     array[]
     *         ['db_table']              string The name of the table [default: sessions]
     *         ['db_id_col']             string The column where to store the session id [default: sess_id]
     *         ['db_data_col']           string The column where to store the session data [default: sess_data]
     *         ['db_lifetime_col']       string The column where to store the lifetime [default: sess_lifetime]
     *         ['db_time_col']           string The column where to store the timestamp [default: sess_time]
     *         ['db_username']           string The username when lazy-connect [default: '']
     *         ['db_password']           string The password when lazy-connect [default: '']
     *         ['db_connection_options'] array  An array of driver-specific connection options [default: array()]
     *         ['lock_mode']             int    The strategy for locking, see constants [default: LOCK_TRANSACTIONAL]
     *
     * @param \PDO|string $pdoOrDsn A \PDO instance or DSN string
     * @param int         $lifetime The session lifetime in seconds
     * @param array       $options  An associative array of options
     *
     * @throws \Viserio\Component\Contract\Session\Exception\InvalidArgumentException When PDO error mode is not PDO::ERRMODE_EXCEPTION
     */
    public function __construct($pdoOrDsn, int $lifetime, array $options = [])
    {
        if ($pdoOrDsn instanceof PDO) {
            if (PDO::ERRMODE_EXCEPTION !== $pdoOrDsn->getAttribute(PDO::ATTR_ERRMODE)) {
                throw new InvalidArgumentException(\sprintf(
                        '[%s] requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION))',
                        __CLASS__
                ));
            }

            $this->pdo    = $pdoOrDsn;
            $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        } else {
            $this->dsn = $pdoOrDsn;
        }

        $this->table             = $options['db_table']              ?? $this->table;
        $this->idCol             = $options['db_id_col']             ?? $this->idCol;
        $this->dataCol           = $options['db_data_col']           ?? $this->dataCol;
        $this->lifetimeCol       = $options['db_lifetime_col']       ?? $this->lifetimeCol;
        $this->timeCol           = $options['db_time_col']           ?? $this->timeCol;
        $this->username          = $options['db_username']           ?? $this->username;
        $this->password          = $options['db_password']           ?? $this->password;
        $this->connectionOptions = $options['db_connection_options'] ?? $this->connectionOptions;
        $this->lockMode          = $options['lock_mode']             ?? $this->lockMode;
        $this->lifetime          = $lifetime;
    }

    /**
     * Creates the table to store sessions which can be called once for setup.
     *
     * Session ID is saved in a column of maximum length 128 because that is enough even
     * for a 512 bit configured session.hash_function like Whirlpool. Session data is
     * saved in a BLOB. One could also use a shorter inlined varbinary column
     * if one was sure the data fits into it.
     *
     * @throws \PDOException                                                 When the table already exists
     * @throws \Viserio\Component\Contract\Session\Exception\DomainException When an unsupported PDO driver is used
     */
    public function createTable(): void
    {
        // connect if we are not yet
        $this->getConnection();

        switch ($this->driver) {
            case 'mysql':
                // We use varbinary for the ID column because it prevents unwanted conversions:
                // - character set conversions between server and client
                // - trailing space removal
                // - case-insensitivity
                // - language processing like é == e
                $sql = "CREATE TABLE $this->table ($this->idCol VARBINARY(128) NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol MEDIUMINT NOT NULL, $this->timeCol INTEGER UNSIGNED NOT NULL) COLLATE utf8_bin, ENGINE = InnoDB";

                break;
            case 'sqlite':
                $sql = "CREATE TABLE $this->table ($this->idCol TEXT NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";

                break;
            case 'pgsql':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->dataCol BYTEA NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";

                break;
            case 'oci':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR2(128) NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";

                break;
            case 'sqlsrv':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->dataCol VARBINARY(MAX) NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";

                break;
            default:
                throw new DomainException(\sprintf(
                    'Creating the session table is currently not implemented for PDO driver [%s].',
                    $this->driver
                ));
        }

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * Returns true when the current session exists but expired according to session.gc_maxlifetime.
     *
     * Can be used to distinguish between a new session and one that expired due to inactivity.
     *
     * @return bool Whether current session expired
     */
    public function isSessionExpired(): bool
    {
        return $this->sessionExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        $this->sessionExpired = false;

        if ($this->pdo === null) {
            $this->connect($this->dsn ?: $savePath);
        }

        return parent::open($savePath, $sessionName);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PDOException
     */
    public function read($sessionId): string
    {
        try {
            return parent::read($sessionId);
        } catch (PDOException $e) {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // We delay gc() to close() so that it is executed outside the transactional and blocking read-write process.
        // This way, pruning expired sessions does not block them from being started while the current session is used.
        $this->gcCalled = true;

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PDOException
     */
    public function updateTimestamp($sessionId, $data): bool
    {
        try {
            $updateStmt = $this->pdo->prepare(
                "UPDATE $this->table SET $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id"
            );
            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':lifetime', $this->lifetime, \PDO::PARAM_INT);
            $updateStmt->bindValue(':time', \time(), \PDO::PARAM_INT);
            $updateStmt->execute();
        } catch (PDOException $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->commit();

        while ($unlockStmt = \array_shift($this->unlockStatements)) {
            $unlockStmt->execute();
        }

        if ($this->gcCalled) {
            $this->gcCalled = false;

            // delete the session records that have expired
            $sql = "DELETE FROM $this->table WHERE $this->lifetimeCol < :time - $this->timeCol";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':time', \time(), \PDO::PARAM_INT);
            $stmt->execute();
        }

        if ($this->dsn !== false) {
            $this->pdo = null; // only close lazy-connection
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PDOException
     */
    protected function doDestroy($sessionId): bool
    {
        // delete the record associated with this id
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $sessionId, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $data): bool
    {
        try {
            // We use a single MERGE SQL query when supported by the database.
            $mergeStmt = $this->getMergeStatement($sessionId, $data, $this->lifetime);

            if ($mergeStmt !== null) {
                $mergeStmt->execute();

                return true;
            }

            $updateStmt = $this->pdo->prepare(
                "UPDATE $this->table SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id"
            );
            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
            $updateStmt->bindParam(':lifetime', $this->lifetime, \PDO::PARAM_INT);
            $updateStmt->bindValue(':time', \time(), \PDO::PARAM_INT);
            $updateStmt->execute();

            // When MERGE is not supported, like in Postgres < 9.5, we have to use this approach that can result in
            // duplicate key errors when the same session is written simultaneously (given the LOCK_NONE behavior).
            // We can just catch such an error and re-execute the update. This is similar to a serializable
            // transaction with retry logic on serialization failures but without the overhead and without possible
            // false positives due to longer gap locking.
            if (! $updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->pdo->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)"
                    );
                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                    $insertStmt->bindParam(':lifetime', $this->lifetime, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':time', \time(), \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (PDOException $e) {
                    // Handle integrity violation SQLSTATE 23000 (or a subclass like 23505 in Postgres) for duplicate keys
                    if (\mb_strpos($e->getCode(), '23') === 0) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (PDOException $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * Reads the session data in respect to the different locking strategies.
     *
     * We need to make sure we do not return session data that is already considered garbage according
     * to the session.gc_maxlifetime setting because gc() is called after read() and only sometimes.
     *
     * @param string $sessionId Session ID
     *
     * @return string The session data
     */
    protected function doRead($sessionId): string
    {
        if (self::LOCK_ADVISORY === $this->lockMode) {
            $this->unlockStatements[] = $this->doAdvisoryLock($sessionId);
        }

        $selectSql  = $this->getSelectSql();
        $selectStmt = $this->pdo->prepare($selectSql);
        $selectStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);

        do {
            $selectStmt->execute();
            $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);

            if ($sessionRows) {
                if ($sessionRows[0][1] + $sessionRows[0][2] < \time()) {
                    $this->sessionExpired = true;

                    return '';
                }

                return \is_resource($sessionRows[0][0]) ? \stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
            }

            if (self::LOCK_TRANSACTIONAL === $this->lockMode && $this->driver !== 'sqlite') {
                // In strict mode, session fixation is not possible: new sessions always start with a unique
                // random id, so that concurrency is not possible and this code path can be skipped.
                // Exclusive-reading of non-existent rows does not block, so we need to do an insert to block
                // until other connections to the session are committed.
                try {
                    $insertStmt = $this->pdo->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)"
                    );
                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindValue(':data', '', \PDO::PARAM_LOB);
                    $insertStmt->bindValue(':lifetime', 0, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':time', \time(), \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (PDOException $e) {
                    // Catch duplicate key error because other connection created the session already.
                    // It would only not be the case when the other connection destroyed the session.
                    if ($e->getCode() === 23) {
                        // Retrieve finished session data written by concurrent connection by restarting the loop.
                        // We have to start a new transaction as a failed query will mark the current transaction as
                        // aborted in PostgreSQL and disallow further queries within it.
                        $this->rollback();
                        $this->beginTransaction();

                        continue;
                    }

                    throw $e;
                }
            }

            return '';
        } while (true);
    }

    /**
     * Return a PDO instance.
     *
     * @return \PDO
     */
    private function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect($this->dsn);
        }

        return $this->pdo;
    }

    /**
     * Lazy-connects to the database.
     *
     * @param string $dsn DSN string
     *
     * @return void
     */
    private function connect(string $dsn): void
    {
        $this->pdo = new PDO($dsn, $this->username, $this->password, $this->connectionOptions);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Helper method to begin a transaction.
     *
     * Since SQLite does not support row level locks, we have to acquire a reserved lock
     * on the database immediately. Because of https://bugs.php.net/42766 we have to create
     * such a transaction manually which also means we cannot use PDO::commit or
     * PDO::rollback or PDO::inTransaction for SQLite.
     *
     * Also MySQLs default isolation, REPEATABLE READ, causes deadlock for different sessions
     * due to http://www.mysqlperformanceblog.com/2013/12/12/one-more-innodb-gap-lock-to-avoid/ .
     * So we change it to READ COMMITTED.
     */
    private function beginTransaction(): void
    {
        if (! $this->inTransaction) {
            if ($this->driver === 'sqlite') {
                $this->pdo->exec('BEGIN IMMEDIATE TRANSACTION');
            } else {
                if ($this->driver === 'mysql') {
                    $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                }

                $this->pdo->beginTransaction();
            }

            $this->inTransaction = true;
        }
    }

    /**
     * Helper method to commit a transaction.
     *
     * @throws \PDOException
     */
    private function commit(): void
    {
        if ($this->inTransaction) {
            try {
                // commit read-write transaction which also releases the lock
                if ($this->driver === 'sqlite') {
                    $this->pdo->exec('COMMIT');
                } else {
                    $this->pdo->commit();
                }

                $this->inTransaction = false;
            } catch (PDOException $e) {
                $this->rollback();

                throw $e;
            }
        }
    }

    /**
     * Helper method to rollback a transaction.
     */
    private function rollback(): void
    {
        // We only need to rollback if we are in a transaction. Otherwise the resulting
        // error would hide the real problem why rollback was called. We might not be
        // in a transaction when not using the transactional locking behavior or when
        // two callbacks (e.g. destroy and write) are invoked that both fail.
        if ($this->inTransaction) {
            if ($this->driver === 'sqlite') {
                $this->pdo->exec('ROLLBACK');
            } else {
                $this->pdo->rollBack();
            }

            $this->inTransaction = false;
        }
    }

    /**
     * Executes an application-level lock on the database.
     *
     * @todo implement missing advisory locks
     *       - for oci using DBMS_LOCK.REQUEST
     *       - for sqlsrv using sp_getapplock with LockOwner = Session
     *
     * @param string $sessionId
     *
     * @throws \DomainException When an unsupported PDO driver is used
     *
     * @return \PDOStatement The statement that needs to be executed later to release the lock
     */
    private function doAdvisoryLock(string $sessionId)
    {
        switch ($this->driver) {
            case 'mysql':
                // MySQL 5.7.5 and later enforces a maximum length on lock names of 64 characters. Previously, no limit was enforced.
                $lockId = \hash('haval256,4', $sessionId);

                // should we handle the return value? 0 on timeout, null on error
                // we use a timeout of 50 seconds which is also the default for innodb_lock_wait_timeout
                $stmt = $this->pdo->prepare('SELECT GET_LOCK(:key, 50)');
                $stmt->bindValue(':key', $lockId, \PDO::PARAM_STR);
                $stmt->execute();

                $releaseStmt = $this->pdo->prepare('DO RELEASE_LOCK(:key)');
                $releaseStmt->bindValue(':key', $lockId, \PDO::PARAM_STR);

                return $releaseStmt;
            case 'pgsql':
                // Obtaining an exclusive session level advisory lock requires an integer key.
                // When session.sid_bits_per_character > 4, the session id can contain non-hex-characters.
                // So we cannot just use hexdec().
                if (4 === \PHP_INT_SIZE) {
                    $sessionInt1 = $this->convertStringToInt($sessionId);
                    $sessionInt2 = $this->convertStringToInt(mb_substr($sessionId, 4, 4));

                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key1, :key2)');
                    $stmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $stmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                    $stmt->execute();

                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key1, :key2)');
                    $releaseStmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $releaseStmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                } else {
                    $sessionBigInt = $this->convertStringToInt($sessionId);

                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key)');
                    $stmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                    $stmt->execute();

                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key)');
                    $releaseStmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                }

                return $releaseStmt;
            case 'sqlite':
                throw new DomainException('SQLite does not support advisory locks.');
            default:
                throw new DomainException(\sprintf('Advisory locks are currently not implemented for PDO driver [%s].', $this->driver));
        }
    }

    /**
     * Return a locking or nonlocking SQL query to read session information.
     *
     * @throws \DomainException When an unsupported PDO driver is used
     */
    private function getSelectSql(): string
    {
        if (self::LOCK_TRANSACTIONAL === $this->lockMode) {
            $this->beginTransaction();

            switch ($this->driver) {
                case 'mysql':
                case 'oci':
                case 'pgsql':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id FOR UPDATE";
                case 'sqlsrv':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WITH (UPDLOCK, ROWLOCK) WHERE $this->idCol = :id";
                case 'sqlite':
                    // we already locked when starting transaction
                    break;
                default:
                    throw new DomainException(\sprintf('Transactional locks are currently not implemented for PDO driver [%s].', $this->driver));
            }
        }

        return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id";
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) statement when supported by the database for writing session data.
     *
     * @param string $sessionId
     * @param string $data
     * @param int    $maxlifetime
     *
     * @return null|\PDOStatement
     */
    private function getMergeStatement(string $sessionId, string $data, int $maxlifetime): ?\PDOStatement
    {
        $mergeSql = null;
        switch (true) {
            case 'mysql' === $this->driver:
                $mergeSql = "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) " .
                    "ON DUPLICATE KEY UPDATE $this->dataCol = VALUES($this->dataCol), $this->lifetimeCol = VALUES($this->lifetimeCol), $this->timeCol = VALUES($this->timeCol)";

                break;
            case 'oci' === $this->driver:
                // DUAL is Oracle specific dummy table
                $mergeSql = "MERGE INTO $this->table USING DUAL ON ($this->idCol = ?) " .
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (?, ?, ?, ?) " .
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = ?, $this->lifetimeCol = ?, $this->timeCol = ?";

                break;
            case 'sqlsrv' === $this->driver && \version_compare($this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '10', '>='):
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                $mergeSql = "MERGE INTO $this->table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($this->idCol = ?) " .
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (?, ?, ?, ?) " .
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = ?, $this->lifetimeCol = ?, $this->timeCol = ?;";

                break;
            case 'sqlite' === $this->driver:
                $mergeSql = "INSERT OR REPLACE INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)";

                break;
            case 'pgsql' === $this->driver && \version_compare($this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '9.5', '>='):
                $mergeSql = "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) " .
                    "ON CONFLICT ($this->idCol) DO UPDATE SET ($this->dataCol, $this->lifetimeCol, $this->timeCol) = (EXCLUDED.$this->dataCol, EXCLUDED.$this->lifetimeCol, EXCLUDED.$this->timeCol)";

                break;
        }

        if ($mergeSql !== null) {
            $mergeStmt = $this->pdo->prepare($mergeSql);

            if ($this->driver === 'sqlsrv' || $this->driver === 'oci') {
                $mergeStmt->bindParam(1, $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(2, $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(3, $data, \PDO::PARAM_LOB);
                $mergeStmt->bindParam(4, $maxlifetime, \PDO::PARAM_INT);
                $mergeStmt->bindValue(5, \time(), \PDO::PARAM_INT);
                $mergeStmt->bindParam(6, $data, \PDO::PARAM_LOB);
                $mergeStmt->bindParam(7, $maxlifetime, \PDO::PARAM_INT);
                $mergeStmt->bindValue(8, \time(), \PDO::PARAM_INT);
            } else {
                $mergeStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                $mergeStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                $mergeStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
                $mergeStmt->bindValue(':time', \time(), \PDO::PARAM_INT);
            }

            return $mergeStmt;
        }
    }

    /**
    +     * Encodes the first 4 (when PHP_INT_SIZE == 4) or 8 characters of the string as an integer.
    +     *
    +     * Keep in mind, PHP integers are signed.
    +     *
    +     * @param string $string
    +     *
    +     * @return int
    +     */
    private function convertStringToInt(string $string): int
    {
        if (4 === \PHP_INT_SIZE) {
            return (\ord($string[3]) << 24) + (\ord($string[2]) << 16) + (\ord($string[1]) << 8) + \ord($string[0]);
        }

        $int1 = (\ord($string[7]) << 24) + (\ord($string[6]) << 16) + (\ord($string[5]) << 8) + \ord($string[4]);
        $int2 = (\ord($string[3]) << 24) + (\ord($string[2]) << 16) + (\ord($string[1]) << 8) + \ord($string[0]);

        return $int2 + ($int1 << 32);
    }
}
