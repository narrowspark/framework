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

interface TransChecker
{
    /**
     * Get the default locale being used.
     */
    public function getDefaultLocale(): string;

    /**
     * Set the locales that need to be checked.
     *
     * @return static
     */
    public function setLocales(array $locales);

    /**
     * Get the locales to check.
     */
    public function getLocales(): array;

    /**
     * Set the locals that are ignored on the check.
     *
     * @return static
     */
    public function setIgnoredTranslations(array $ignored);

    /**
     * Get the ignored translation attributes.
     */
    public function getIgnoredTranslations(): array;

    /**
     * Check the missing translations.
     */
    public function check(): array;
}
