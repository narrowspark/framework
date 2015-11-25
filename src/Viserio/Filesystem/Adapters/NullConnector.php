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

use League\Flysystem\Adapter\NullAdapter;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

/**
 * NullConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3
 */
class NullConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return NullAdapter
     */
    public function connect(array $config)
    {
        return $this->getAdapter();
    }

    /**
     * Get the null adapter.
     *
     * @return \League\Flysystem\Adapter\NullAdapter
     */
    protected function getAdapter()
    {
        return new NullAdapter();
    }
}
