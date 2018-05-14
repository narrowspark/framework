<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Translation;

interface TranslationManager
{
    /**
     * Get a language translator instance.
     *
     * @param null|string $locale
     *
     * @throws \Viserio\Contract\Translation\Exception\RuntimeException
     *
     * @return \Viserio\Contract\Translation\Translator
     */
    public function getTranslator(string $locale = null): Translator;
}
