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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Filesystem\Connector as ConnectorContract;
use League\Flysystem\Adapter\NullAdapter;

/**
 * NullConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
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
