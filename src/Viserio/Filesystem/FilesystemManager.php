<?php
namespace Viserio\Filesystem;

use League\Flysystem\AdapterInterface;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Filesystem\Adapters\ConnectionFactory;
use Viserio\Support\Manager;

class FilesystemManager extends Manager
{
    /**
     * Container instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The factory instance.
     *
     * @var \Viserio\Filesystem\Adapters\ConnectionFactory
     */
    protected $factory;

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    protected $defaultDriver = [
        'awss3'     => 'AwsS3',
        'ftp'       => 'Ftp',
        'local'     => 'Local',
        'null'      => 'Null',
        'rackspace' => 'Rackspace',
        'sftp'      => 'Sftp',
        'zip'       => 'Zip',
    ];

    /**
     * Create a new filesystem manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager              $config
     * @param \Viserio\Filesystem\Adapters\ConnectionFactory $factory
     */
    public function __construct(ConfigContract $config, ConnectionFactory $factory)
    {
        $this->config  = $config;
        $this->factory = $factory;
    }

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->config->set('filesystems::default', $name);

        return $this;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('filesystems::default', '');
    }

    /**
     * Adapt the filesystem implementation.
     *
     * @param \League\Flysystem\AdapterInterface $filesystem
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    protected function adapt(AdapterInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        if (isset($this->defaultDriver[$config['driver']])) {
            return $this->defaultDriver[$config['driver']] . 'Connector';
        }

        throw new InvalidArgumentException(sprintf('Unsupported driver [%s]', $config['driver']));
    }
}
