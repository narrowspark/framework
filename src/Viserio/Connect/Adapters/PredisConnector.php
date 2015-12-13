<?php
namespace Viserio\Connect\Adapters;

use Predis\Client;
use RuntimeException;
use Viserio\Contracts\Connect\Connector as ConnectorContract;

class PredisConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        if (!isset($config['servers'])) {
            throw new RuntimeException('servers config dont exist.');
        }

        $parameters = $config['servers'];
        $options    = null;

        if (isset($config['options'])) {
            $options = $config['options'];
        }

        if (is_array($parameters)) {
            $parameters = array_values($parameters);
        }

        return new Client($parameters, $options);
    }
}
