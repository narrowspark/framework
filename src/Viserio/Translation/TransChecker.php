<?php
namespace Viserio\Translation;

use Viserio\Contracts\Translation\TransChecker as TransCheckerContract;

class TransChecker implements TransCheckerContract
{
    /**
     * The missing translations.
     *
     * @var array
     */
    private $missing = [];

     /**
     * Make TransChecker instance.
     *
     * @param \TranslationManager $manager
     */
    public function __construct(TransManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale(): string
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setLocales(array $locales): TransCheckerContract
    {
        $this->locales = locales;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * {@inheritdoc}
     */
    public function setIgnoredTranslations(array $ignored): TransCheckerContract
    {
        $this->ignored = ignored;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIgnoredTranslations(): array
    {
        return $this->ignored;
    }

    /**
     * {@inheritdoc}
     */
    public function check(): array
    {
        $from = $this->getDefaultLocale();
        $locales = $this->getLocales();
        $ignored = $this->getIgnoredTranslations();
    }

    /**
     * Diff the missing translations.
     *
     * @param array  $toTranslations
     * @param array  $fromTranslations
     * @param string $locale
     *
     * @return array
     */
    private function diffMissing(array $toTranslations, array $fromTranslations, string $locale): array
    {
        $diff = array_diff_key($toTranslations, $fromTranslations);

        if (count($diff) === 0) {
            return;
        }

        foreach ($diff as $transKey => $transValue) {
            $this->addMissing($locale, $transKey);
        }
    }

    /**
     * Adding missing translation to collection.
     *
     * @param string $locale
     * @param string $transKey
     *
     * @return void
     */
    private function addMissing(string $locale, string $transKey)
    {
        if (! $this->hasMissing($locale, $transKey)) {
            $this->missing[$locale][] = $transKey;
        }
    }

    /**
     * Check if a missing translation exists in collection.
     *
     * @param string $locale
     * @param string $transKey
     *
     * @return bool
     */
    private function hasMissing(string $locale, string $transKey): bool
    {
        if (! isset($this->missing[$locale])) {
            return false;
        }

        return in_array($transKey, $this->missing[$locale]);
    }
}
