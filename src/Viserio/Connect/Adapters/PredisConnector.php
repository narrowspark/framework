<?php
namespace Viserio\Connect\Adapters;

use InvalidArgumentException;
use Predis\Client;
use Viserio\Contracts\Support\Connector as ConnectorContract;

class PredisConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        if (! isset($config['servers'])) {
            throw new InvalidArgumentException('servers config don\'t exist.');
        }

        $parameters = $config['servers'];
        $options = null;

        if (isset($config['options'])) {
            $options = $config['options'];
        }

        if (is_array($parameters)) {
            $parameters = array_values($parameters);
        }

        return new Client($parameters, $options);
    }
}
