<?php
namespace Viserio\Translator;

use Viserio\Translator\Traits\ValidateLocaleTrait;

class FallbackResolver
{
    use ValidateLocaleTrait;

    /**
     * Locale to use as fallback if there is no translation.
     *
     * @var array
     */
    protected $fallbackLocales = [];

    /**
     * Get the fallback locale.
     *
     * @return array
     */
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }

    /**
     * Sets the fallback locales.
     *
     * @param array $locales The fallback locales
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     *
     * @return self
     */
    public function setFallbackLocales(array $locales)
    {
        foreach ($locales as $locale) {
            $this->assertValidLocale($locale);
        }

        $this->fallbackLocales = $locales;

        return $this;
    }

    /**
     * Compute fallback locales.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function computeFallbackLocales($locale)
    {
        $locales = [];

        foreach ($this->fallbackLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }

            $locales[] = $fallback;
        }

        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }

        return array_unique($locales);
    }

    /**
     * Load fallback catalogues.
     *
     * @param string $locale
     */
    private function loadFallbackCatalogues($locale)
    {
        $current = $this->catalogues[$locale];

        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                //ToDo add fallback
            }

            $current->addFallbackCatalogue($this->catalogues[$fallback]);
            $current = $this->catalogues[$fallback];
        }
    }
}
