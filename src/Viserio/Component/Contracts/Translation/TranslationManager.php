<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation;

interface TranslationManager
{
    /**
     * Get a language translator instance.
     *
     * @param null|string $locale
     *
     * @throws \Viserio\Component\Contracts\Translation\Exception\RuntimeException
     *
     * @return \Viserio\Component\Contracts\Translation\Translator
     */
    public function getTranslator(string $locale = null): Translator;
}
