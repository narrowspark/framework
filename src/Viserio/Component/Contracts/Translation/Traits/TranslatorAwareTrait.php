<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation\Traits;

use RuntimeException;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;

trait TranslatorAwareTrait
{
    /**
     * Translation instance.
     *
     * @var \Viserio\Component\Contracts\Translation\Translator|null
     */
    protected $translator;

    /**
     * Set a translation instance.
     *
     * @param \Viserio\Component\Contracts\Translation\Translator $translator
     *
     * @return $this
     */
    public function setTranslator(TranslatorContract $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Get the translation instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Translation\Translator
     */
    public function getTranslator(): TranslatorContract
    {
        if (! $this->translator) {
            throw new RuntimeException('Translator is not set up.');
        }

        return $this->translator;
    }
}
