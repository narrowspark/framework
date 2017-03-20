<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;

class ConnectionFactory
{
    /**
     * List of types config.
     *
     * @var array
     */
    private $typesConfig = [];

    /**
     * List of commented types.
     *
     * @var array
     */
    private $commentedTypes = [];

    /**
     * Is initialized.
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * Create a new connection factory instance.
     *
     * @param array $typesConfig
     */
    public function __construct(array $typesConfig)
    {
        $this->typesConfig = $typesConfig;
    }

    /**
     * Create a connection by name.
     *
     * @param array                         $params
     * @param \Doctrine\DBAL\Configuration  $config
     * @param \Doctrine\Common\EventManager $eventManager
     * @param array                         $mappingTypes
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ): Connection {
        if (! $this->initialized) {
            $this->initializeTypes();
        }

        $connection = DriverManager::getConnection($params, $config, $eventManager);

        if (! empty($mappingTypes)) {
            $platform = $connection->getDatabasePlatform();

            foreach ($mappingTypes as $dbType => $doctrineType) {
                $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }

        if (!empty($this->commentedTypes)) {
            $platform = $connection->getDatabasePlatform();

            foreach ($this->commentedTypes as $type) {
                $platform->markDoctrineTypeCommented(Type::getType($type));
            }
        }

        return $connection;
    }

    /**
     * Initialize the types.
     *
     * @return void
     */
    private function initializeTypes(): void
    {
        foreach ($this->typesConfig as $type => $typeConfig) {
            if (Type::hasType($type)) {
                Type::overrideType($type, $typeConfig['class']);
            } else {
                Type::addType($type, $typeConfig['class']);
            }

            if ($typeConfig['commented']) {
                $this->commentedTypes[] = $type;
            }
        }

        $this->initialized = true;
    }
}
