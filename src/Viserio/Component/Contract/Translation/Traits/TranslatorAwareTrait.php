<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Translation\Traits;

use RuntimeException;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;

trait TranslatorAwareTrait
{
    /**
     * Translation instance.
     *
     * @var null|\Viserio\Component\Contract\Translation\Translator
     */
    protected $translator;

    /**
     * Set a translation instance.
     *
     * @param \Viserio\Component\Contract\Translation\Translator $translator
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
     * @return \Viserio\Component\Contract\Translation\Translator
     */
    public function getTranslator(): TranslatorContract
    {
        if (! $this->translator) {
            throw new RuntimeException('Translator is not set up.');
        }

        return $this->translator;
    }
}
