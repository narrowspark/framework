<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Commands;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ArrayConnectionConfigurationLoader;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ConnectionConfigurationChainLoader;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ConnectionConfigurationLoader;
use Doctrine\DBAL\Migrations\Configuration\Connection\Loader\ConnectionHelperLoader;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Bridge\Doctrine\Migration\Commands\Helper\ConfigurationHelper;
use Viserio\Bridge\Doctrine\Migration\Configuration\Configuration;
use Viserio\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * @var \Viserio\Bridge\Doctrine\Migration\Configuration\Configuration
     */
    private $configuration;
    private $migrationConfiguration;

    /**
     * When any (config) command line option is passed to the migration the migrationConfiguration
     * property is set with the new generated configuration.
     * If no (config) option is passed the migrationConfiguration property is set to the value
     * of the configuration one (if any).
     * Else a new configuration is created and assigned to the migrationConfiguration property.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return \Viserio\Bridge\Doctrine\Migration\Configuration\Configuration
     */
    protected function getMigrationConfiguration(InputInterface $input, OutputInterface $output): Configuration
    {
        if (! $this->migrationConfiguration) {
            if ($this->getHelperSet()->has('migration_configuration')) {
                $configHelper = $this->getHelperSet()->get('migration_configuration');

                if (! $configHelper instanceof ConfigurationHelper) {
                    throw new RuntimeException('');
                }
            } else {
                $configHelper = new ConfigurationHelper($this->configuration, $this->getConnection($input));
            }

            $configHelper->setContainer($this->container);

            $this->migrationConfiguration = $configHelper->getMigrationConfig($input, $this->getOutputWriter($output));
        }

        return $this->migrationConfiguration;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Doctrine\DBAL\Connection
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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Doctrine\DBAL\Migrations\OutputWriter
     */
    private function getOutputWriter(OutputInterface $output)
    {
        if (! $this->outputWriter) {
            $this->outputWriter = new OutputWriter(function ($message) use ($output) {
                return $output->writeln($message);
            });
        }

        return $this->outputWriter;
    }
}
