<?php
declare(strict_types=1);
namespace Viserio\Contracts\Translation\Traits;

use RuntimeException;
use Viserio\Contracts\Translation\Translator as TranslatorContract;

trait TranslationAwareTrait
{
    /**
     * Translation instance.
     *
     * @var \Viserio\Contracts\Translation\Translator|null
     */
    protected $translation;

    /**
     * Set a translation instance.
     *
     * @param \Viserio\Contracts\Translation\Translator $translation
     *
     * @return $this
     */
    public function setTranslator(TranslatorContract $translation)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get the translation instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Translation\Translator
     */
    public function getTranslator(): TranslatorContract
    {
        if (! $this->translation) {
            throw new RuntimeException('Translator is not set up.');
        }

        return $this->translation;
    }
}
