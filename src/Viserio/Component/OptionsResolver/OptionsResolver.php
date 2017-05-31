<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class OptionsResolver
{
    use OptionsResolverTrait;

    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveConfiguration($data)
    {
        if (is_iterable($data)) {
            return $data;
        } elseif ($data instanceof ContainerInterface) {
            if ($data->has(RepositoryContract::class)) {
                return $data->get(RepositoryContract::class);
            } elseif ($data->has('config')) {
                return $data->get('config');
            } elseif ($data->has('options')) {
                return $data->get('options');
            }
        }

        throw new RuntimeException('No configuration found.');
    }
}
