<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Translation;

interface TranslationManager
{
    /**
     * Get a language translator instance.
     *
     * @param null|string $locale
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\RuntimeException
     *
     * @return \Viserio\Component\Contract\Translation\Translator
     */
    public function getTranslator(string $locale = null): Translator;
}
