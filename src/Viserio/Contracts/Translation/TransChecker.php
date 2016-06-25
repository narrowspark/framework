<?php
namespace Viserio\Contracts\Translation;

interface TransChecker
{
    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Get the locales to check.
     *
     * @return array
     */
    public function getLocales(): array;

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
