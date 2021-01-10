<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Translation;

interface TranslationManager
{
    /**
     * Get a language translator instance.
     *
     * @throws \Viserio\Contract\Translation\Exception\RuntimeException
     *
     * @return \Viserio\Contract\Translation\Translator
     */
    public function getTranslator(?string $locale = null): Translator;
}
