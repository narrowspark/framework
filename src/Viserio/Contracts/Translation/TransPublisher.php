<?php
namespace Viserio\Contracts\Translation;

interface TransPublisher
{
    /**
     * Publish a lang.
     *
     * @param string $localeKey
     * @param bool   $force
     *
     * @return bool
     *
     * @throws \Viserio\Contracts\Translation\Exception\LangPublishException
     */
    public function publish(string $localeKey, bool $force = false): bool;

    /**
     * Check if the locale is a default one (English is shipped with laravel).
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isDefault(string $locale): bool;

    /**
     * Check if the locale is supported.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isSupported(string $key): bool;
}
