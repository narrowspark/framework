<?php
namespace Viserio\Queue\Failed;

use Viserio\Contracts\Queue\FailedJobProvider as FailedJobProviderContract;

class NullFailedJobProvider implements FailedJobProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function log(string $connection, string $queue, string $payload)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function find($id): array
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        //
    }
}
