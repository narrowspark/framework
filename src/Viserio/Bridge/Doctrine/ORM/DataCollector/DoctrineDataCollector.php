<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Types\Type;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class DoctrineDataCollector extends AbstractDataCollector implements
    TooltipAwareContract,
    PanelAwareContract
{
    private $registry;

    private $connections;

    private $managers;

    /**
     * A list of all sql logger.
     *
     * @var array
     */
    private $loggers = [];

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry    = $registry;
        $this->connections = $registry->getConnectionNames();
        $this->managers    = $registry->getManagerNames();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $queries  = [];
        $errors   = [];
        $entities = [];
        $caches   = [
            'enabled'     => false,
            'log_enabled' => false,
            'counts'      => [
                'puts'   => 0,
                'hits'   => 0,
                'misses' => 0,
            ],
            'regions' => [
                'puts'   => [],
                'hits'   => [],
                'misses' => [],
            ],
        ];

        foreach ($this->loggers as $name => $logger) {
            $queries[$name] = $this->sanitizeQueries($name, $logger->queries);
        }

        foreach ($this->registry->getManagers() as $name => $em) {
            $entities[$name] = [];

            /** @var $factory \Doctrine\ORM\Mapping\ClassMetadataFactory */
            $factory   = $em->getMetadataFactory();
            $validator = new SchemaValidator($em);

            /** @var $class \Doctrine\ORM\Mapping\ClassMetadataInfo */
            foreach ($factory->getLoadedMetadata() as $class) {
                if (! isset($entities[$name][$class->getName()])) {
                    $classErrors                        = $validator->validateClass($class);
                    $entities[$name][$class->getName()] = $class->getName();

                    if (! empty($classErrors)) {
                        $errors[$name][$class->getName()] = $classErrors;
                    }
                }
            }

            /** @var $emConfig \Doctrine\ORM\Configuration */
            $emConfig   = $em->getConfiguration();
            $slcEnabled = $emConfig->isSecondLevelCacheEnabled();

            if (! $slcEnabled) {
                continue;
            }

            $caches['enabled'] = true;

            /** @var $cacheConfiguration \Doctrine\ORM\Cache\CacheConfiguration */
            /** @var $cacheLoggerChain \Doctrine\ORM\Cache\Logging\CacheLoggerChain */
            $cacheConfiguration = $emConfig->getSecondLevelCacheConfiguration();
            $cacheLoggerChain   = $cacheConfiguration->getCacheLogger();

            if (! $cacheLoggerChain || ! $cacheLoggerChain->getLogger('statistics')) {
                continue;
            }

            /** @var $cacheLoggerStats \Doctrine\ORM\Cache\Logging\StatisticsCacheLogger */
            $cacheLoggerStats      = $cacheLoggerChain->getLogger('statistics');
            $caches['log_enabled'] = true;
            $caches['counts']['puts'] += $cacheLoggerStats->getPutCount();
            $caches['counts']['hits'] += $cacheLoggerStats->getHitCount();
            $caches['counts']['misses'] += $cacheLoggerStats->getMissCount();

            foreach ($cacheLoggerStats->getRegionsPut() as $key => $value) {
                if (! isset($caches['regions']['puts'][$key])) {
                    $caches['regions']['puts'][$key] = 0;
                }

                $caches['regions']['puts'][$key] += $value;
            }

            foreach ($cacheLoggerStats->getRegionsHit() as $key => $value) {
                if (! isset($caches['regions']['hits'][$key])) {
                    $caches['regions']['hits'][$key] = 0;
                }

                $caches['regions']['hits'][$key] += $value;
            }

            foreach ($cacheLoggerStats->getRegionsMiss() as $key => $value) {
                if (! isset($caches['regions']['misses'][$key])) {
                    $caches['regions']['misses'][$key] = 0;
                }

                $caches['regions']['misses'][$key] += $value;
            }
        }

        $this->data = [
            'queries'     => $queries,
            'connections' => $this->connections,
            'managers'    => $this->managers,
            'entities'    => $entities,
            'errors'      => $errors,
            'caches'      => $caches,
        ];
    }

    /**
     * Adds the stack logger for a connection.
     *
     * @param string                            $name
     * @param \Doctrine\DBAL\Logging\DebugStack $logger
     */
    public function addLogger(string $name, DebugStack $logger)
    {
        $this->loggers[$name] = $logger;
    }

    public function getInvalidEntityCount()
    {
        if (null === $this->invalidEntityCount) {
            $this->invalidEntityCount = array_sum(array_map('count', $this->data['errors']));
        }

        return $this->invalidEntityCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        return '';
    }

    private function getQueryCount()
    {
        return array_sum(array_map('count', $this->data['queries']));
    }

    private function getTime()
    {
        $time = 0;

        foreach ($this->data['queries'] as $queries) {
            foreach ($queries as $query) {
                $time += $query['executionMS'];
            }
        }

        return $time;
    }

    private function getGroupedQueryCount()
    {
        $count = 0;

        foreach ($this->getGroupedQueries() as $connectionGroupedQueries) {
            $count += count($connectionGroupedQueries);
        }

        return $count;
    }

    private function getGroupedQueries()
    {
        static $groupedQueries = null;

        if ($groupedQueries !== null) {
            return $groupedQueries;
        }

        $groupedQueries   = [];
        $totalExecutionMS = 0;

        foreach ($this->data['queries'] as $connection => $queries) {
            $connectionGroupedQueries = [];

            foreach ($queries as $i => $query) {
                $key = $query['sql'];

                if (! isset($connectionGroupedQueries[$key])) {
                    $connectionGroupedQueries[$key]                = $query;
                    $connectionGroupedQueries[$key]['executionMS'] = 0;
                    $connectionGroupedQueries[$key]['count']       = 0;
                    $connectionGroupedQueries[$key]['index']       = $i; // "Explain query" relies on query index in 'queries'.
                }

                $connectionGroupedQueries[$key]['executionMS'] += $query['executionMS'];
                ++$connectionGroupedQueries[$key]['count'];
                $totalExecutionMS += $query['executionMS'];
            }

            usort($connectionGroupedQueries, function ($a, $b) {
                if ($a['executionMS'] === $b['executionMS']) {
                    return 0;
                }

                return ($a['executionMS'] < $b['executionMS']) ? 1 : -1;
            });

            $groupedQueries[$connection] = $connectionGroupedQueries;
        }

        foreach ($groupedQueries as $connection => $queries) {
            foreach ($queries as $i => $query) {
                $groupedQueries[$connection][$i]['executionPercent'] = $this->executionTimePercentage($query['executionMS'], $totalExecutionMS);
            }
        }

        return $groupedQueries;
    }

    private function sanitizeQueries(string $connectionName, array $queries): array
    {
        foreach ($queries as $i => $query) {
            $queries[$i] = $this->sanitizeQuery($connectionName, $query);
        }

        return $queries;
    }

    private function sanitizeQuery(string $connectionName, array $query): array
    {
        $query['explainable'] = true;

        if (null === $query['params']) {
            $query['params'] = [];
        }

        if (! is_array($query['params'])) {
            $query['params'] = [$query['params']];
        }

        foreach ($query['params'] as $j => $param) {
            if (isset($query['types'][$j])) {
                // Transform the param according to the type
                $type = $query['types'][$j];

                if (is_string($type)) {
                    $type = Type::getType($type);
                }

                if ($type instanceof Type) {
                    $query['types'][$j] = $type->getBindingType();
                    $param              = $type->convertToDatabaseValue($param, $this->registry->getConnection($connectionName)->getDatabasePlatform());
                }
            }

            list($query['params'][$j], $explainable) = $this->sanitizeParam($param);

            if (! $explainable) {
                $query['explainable'] = false;
            }
        }

        return $query;
    }

    /**
     * Sanitizes a param.
     *
     * The return value is an array with the sanitized value and a boolean
     * indicating if the original value was kept (allowing to use the sanitized
     * value to explain the query).
     *
     * @param mixed $var
     *
     * @return array
     */
    private function sanitizeParam($var): array
    {
        if (is_object($var)) {
            $className = get_class($var);

            return method_exists($var, '__toString') ?
                [sprintf('Object(%s): "%s"', $className, $var->__toString()), false] :
                [sprintf('Object(%s)', $className), false];
        }

        if (is_array($var)) {
            $a        = [];
            $original = true;

            foreach ($var as $k => $v) {
                list($value, $orig) = $this->sanitizeParam($v);
                $original           = $original && $orig;
                $a[$k]              = $value;
            }

            return [$a, $original];
        }

        if (is_resource($var)) {
            return [sprintf('Resource(%s)', get_resource_type($var)), false];
        }

        return [$var, true];
    }

    private function executionTimePercentage($executionTimeMS, $totalExecutionTimeMS)
    {
        if ($totalExecutionTimeMS === 0.0 || $totalExecutionTimeMS === 0) {
            return 0;
        }

        return $executionTimeMS / $totalExecutionTimeMS * 100;
    }
}
