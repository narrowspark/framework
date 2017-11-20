<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Commands;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ArrayConnectionConfigurationLoader;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ConnectionConfigurationLoader;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ConnectionHelperLoader;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ConnectionConfigurationChainLoader;
use LaravelDoctrine\Migrations\Naming\DefaultNamingStrategy;
use Symfony\Component\Console\Input\InputInterface;
use Viserio\Bridge\Doctrine\Contract\Migration\Exception\InvalidArgumentException;
use Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy as NamingStrategyContract;
use Viserio\Bridge\Doctrine\Migration\Configuration\Configuration;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

abstract class AbstractCommand extends Command implements
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * Doctrine config.
     *
     * @var null|iterable
     */
    private $options;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function __construct(iterable $options = null)
    {
        parent::__construct();

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', 'migration'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'default' => [
                'name'                => 'Doctrine Migrations',
                'namespace'           => 'Database\\Migration',
                'table'               => 'migrations',
                'schema_filter'       => '/^(?).*$/',
                'naming_strategy'     => null,
                'custom_template'     => null,
                'organize_migrations' => null
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['directory'];
    }

    /**
     * Get a viserio migration configuration object.
     *
     * @param \Doctrine\DBAL\Connection $connection
     * @param string                    $name
     *
     * @return \Viserio\Bridge\Doctrine\Migration\Configuration\Configuration
     */
    protected function getConfiguration(Connection $connection, string $name = 'default'): Configuration
    {
        $config = self::resolveOptions($this->options ?? $this->getContainer(), $name);

        $configuration = new Configuration($connection);

        $configuration->setName($config['name']);
        $configuration->setMigrationsNamespace($config['namespace']);
        $configuration->setMigrationsTableName($config['table']);
        $configuration->getConnection()->getConfiguration()->setFilterSchemaAssetsExpression($config['schema_filter']);

        $strategy = $this->getStrategy($config);

        $configuration->setNamingStrategy($strategy);
        $configuration->setMigrationsFinder($strategy->getFinder());

        $configuration->setMigrationsDirectory($config['directory']);
        $configuration->registerMigrationsFromDirectory($config['directory']);
        $configuration->setCustomTemplate($config['custom_template']);

        return $this->configureOrganizeMigrations($config, $configuration);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getConnection(InputInterface $input)
    {
        if ($this->connection) {
            return $this->connection;
        }

        $chainLoader = new ConnectionConfigurationChainLoader(
            [
                new ArrayConnectionConfigurationLoader($input->getOption('db-configuration')),
                new ArrayConnectionConfigurationLoader('migrations-db.php'),
                new ConnectionHelperLoader($this->getHelperSet(), 'connection'),
                new ConnectionConfigurationLoader($this->configuration),
            ]
        );

        if ($connection  = $chainLoader->chosen()) {
            return $this->connection = $connection;
        }

        throw new \InvalidArgumentException('You have to specify a --db-configuration file or pass a Database Connection as a dependency to the Migrations.');
    }

    /**
     * @param array $config
     *
     * @return \Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy
     */
    private function getStrategy(array $config): NamingStrategyContract
    {
        if (\is_object($config['naming_strategy'])) {
            return $config['naming_strategy'];
        }

        if ($this->container !== null && $this->getContainer()->has($config['naming_strategy'])) {
            return $this->getContainer()->get($config['naming_strategy']);
        }

        return new DefaultNamingStrategy();
    }

    /**
     * @param iterable $config
     * @param \Viserio\Bridge\Doctrine\Migration\Configuration\Configuration $configuration
     *
     * @throws \Viserio\Bridge\Doctrine\Contract\Migration\Exception\InvalidArgumentException
     *
     * @return \Viserio\Bridge\Doctrine\Migration\Configuration\Configuration
     */
    private function configureOrganizeMigrations(iterable $config, Configuration $configuration): Configuration
    {
        switch ($config['organize_migrations']) {
            case Configuration::VERSIONS_ORGANIZATION_BY_YEAR:
                $configuration->setMigrationsAreOrganizedByYear(true);
                break;

            case Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH:
                $configuration->setMigrationsAreOrganizedByYearAndMonth(true);
                break;

            case null:
                break;

            default:
                throw new InvalidArgumentException('Invalid value for [organize_migrations].');
        }

        return $configuration;
    }
}