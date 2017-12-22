<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;

final class DropboxConnector implements ConnectorContract
{
    use GetSelectedConfigTrait;

    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        if (! \array_key_exists('token', $config)) {
            throw new InvalidArgumentException('The dropbox connector requires authentication token.');
        }

        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = '';
        }

        $config = self::getSelectedConfig($config, ['prefix', 'token']);

        return new DropboxAdapter(
            new Client($config['token']),
            $config['prefix']
        );
    }
}
