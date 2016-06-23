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
     * {@inheritdoc}
     */
    public function getDefaultLocale(): string
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): array
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getIgnoredTranslations(): array
    {

    }

    /**
     * {@inheritdoc}
     */
    public function check(): array
    {

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
