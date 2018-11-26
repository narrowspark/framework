<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Commands\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\OutputWriter;
use Symfony\Component\Console\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Viserio\Bridge\Doctrine\Contract\Migration\Exception\InvalidArgumentException;
use Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy as NamingStrategyContract;
use Viserio\Bridge\Doctrine\Migration\Configuration\Configuration;
use Viserio\Bridge\Doctrine\Migration\Naming\DefaultNamingStrategy;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ConfigurationHelper extends Helper implements
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;
    use ContainerAwareTrait;

    /**
     * Doctrine config.
     *
     * @var iterable
     */
    private $options;

    /**
     * Connection instance to use for migrations.
     *
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * ConfigurationHelper constructor.
     *
     * @param iterable                  $options
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(iterable $options, Connection $connection)
    {
        $this->options    = $options;
        $this->connection = $connection;
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
            'default'     => 'mysql',
            'connections' => [
                'mysql' => [
                    'name'                => 'Doctrine Migrations',
                    'namespace'           => 'Database\\Migration',
                    'table'               => 'migrations',
                    'schema_filter'       => '/^(?).*$/',
                    'naming_strategy'     => null,
                    'custom_template'     => null,
                    'organize_migrations' => null,
                ],
            ],
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'migration_configuration';
    }

    /**
     * Get a viserio migration configuration object.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Doctrine\DBAL\Migrations\OutputWriter          $outputWriter
     *
     * @return \Viserio\Bridge\Doctrine\Migration\Configuration\Configuration
     */
    public function getMigrationConfig(InputInterface $input, OutputWriter $outputWriter): Configuration
    {
        $options = self::resolveOptions($this->options);

        try {
            $name = $input->getOption('connection');
        } catch (SymfonyInvalidArgumentException $exception) {
            $name = $options['default'];
        }

        $configuration = new Configuration($this->connection, $outputWriter);
        $config        = $options['connections'][$name];

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
     * Get a naming strategy.
     *
     * @param array $config
     *
     * @return \Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy
     */
    private function getStrategy(array $config): NamingStrategyContract
    {
        if (\is_object($config['naming_strategy'])) {
            return $config['naming_strategy'];
        }

        if ($this->container !== null && $this->container->has($config['naming_strategy'])) {
            return $this->container->get($config['naming_strategy']);
        }

        return new DefaultNamingStrategy();
    }

    /**
     * Configures the migration organize.
     *
     * @param iterable                                                       $config
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
