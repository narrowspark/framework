<?php
namespace Viserio\Translation;

use Viserio\Contracts\Translation\TransPublisher as TransPublisherContract;

class TransPublisher implements TransPublisherContract
{
    /**
     * {@inheritdoc}
     */
    public function publish(string $localeKey, bool $force = false): bool
    {

    }

    /**
     * {@inheritdoc}
     */
    public function isDefault(string $locale): bool
    {

    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(string $key): bool
    {

    }
}
