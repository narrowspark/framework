<?php
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\Adapter\Ftp;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

/**
 * FtpConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class FtpConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return Ftp
     */
    public function connect(array $config)
    {
        return $this->getAdapter($config);
    }
    /**
     * Get the ftp adapter.
     *
     * @param array $config
     *
     * @return \League\Flysystem\Adapter\Ftp
     */
    protected function getAdapter(array $config)
    {
        return new Ftp($config);
    }
}
