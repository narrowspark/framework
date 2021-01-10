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

namespace Viserio\Contract\Translation\Traits;

use Viserio\Contract\Translation\Translator as TranslatorContract;

trait TranslatorAwareTrait
{
    /**
     * Translation instance.
     *
     * @var null|\Viserio\Contract\Translation\Translator
     */
    protected $translator;

    /**
     * Set a translation instance.
     *
     * @return static
     */
    public function setTranslator(TranslatorContract $translator): self
    {
        $this->translator = $translator;

        return $this;
    }
}
