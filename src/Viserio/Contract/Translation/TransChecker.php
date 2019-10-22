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

interface TransChecker
{
    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Set the locales that need to be checked.
     *
     * @param array $locales
     *
     * @return static
     */
    public function setLocales(array $locales);

    /**
     * Get the locales to check.
     *
     * @return array
     */
    public function getLocales(): array;

    /**
     * Set the locals that are ignored on the check.
     *
     * @param array $ignored
     *
     * @return static
     */
    public function setIgnoredTranslations(array $ignored);

    /**
     * Get the ignored translation attributes.
     *
     * @return array
     */
    public function getIgnoredTranslations(): array;

    /**
     * Check the missing translations.
     *
     * @return array
     */
    public function check(): array;
}
