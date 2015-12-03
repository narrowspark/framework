<?php
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\Sftp\SftpAdapter;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

class SftpConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return object
     */
    public function connect(array $config)
    {
        return $this->getAdapter($config);
    }

    /**
     * Get the sftp adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\Sftp\SftpAdapter
     */
    protected function getAdapter(array $config)
    {
        return new SftpAdapter($config);
    }
}
