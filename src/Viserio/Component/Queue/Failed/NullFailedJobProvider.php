<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Failed;

use Viserio\Component\Contract\Queue\FailedJobProvider as FailedJobProviderContract;

class NullFailedJobProvider implements FailedJobProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function log(string $connection, string $queue, string $payload): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function find($id): array
    {
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
    public function clear(): void
    {
    }
}
