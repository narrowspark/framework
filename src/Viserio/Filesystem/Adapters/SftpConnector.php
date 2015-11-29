<?php
namespace Viserio\Filesystem\Adapters;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use League\Flysystem\Sftp\SftpAdapter;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

/**
 * NullConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3
 */
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
