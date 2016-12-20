<?php
declare(strict_types=1);
namespace Viserio\Contracts\Translation\Traits;

use RuntimeException;
use Viserio\Contracts\Translation\Translator as TranslatorContract;

trait TranslatorAwareTrait
{
    /**
     * Translation instance.
     *
     * @var \Viserio\Contracts\Translation\Translator|null
     */
    protected $translator;

    /**
     * Set a translation instance.
     *
     * @param \Viserio\Contracts\Translation\Translator $translator
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
     * @return \Viserio\Contracts\Translation\Translator
     */
    public function getTranslator(): TranslatorContract
    {
        if (!$this->translator) {
            throw new RuntimeException('Translator is not set up.');
        }

        return $this->translator;
    }
}
