<?php
namespace Brainwave\Filesystem\Adapters;

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
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Filesystem\Connector as ConnectorContract;
use League\Flysystem\Adapter\Ftp;

/**
 * FtpConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
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
