<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Translation;

interface TransChecker
{
    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Set the locales that need to be checked.
     *
     * @param array $locales
     *
     * @return $this
     */
    public function setLocales(array $locales): TransChecker;

    /**
     * Get the locales to check.
     *
     * @return array
     */
    public function getLocales(): array;

    /**
     * Set the locals that are ignored on the check.
     *
     * @param array $ignored
     *
     * @return $this
     */
    public function setIgnoredTranslations(array $ignored): TransChecker;

    /**
     * Get the ignored translation attributes.
     *
     * @return array
     */
    public function getIgnoredTranslations(): array;

    /**
     * Check the missing translations.
     *
     * @return array
     */
    public function check(): array;
}
