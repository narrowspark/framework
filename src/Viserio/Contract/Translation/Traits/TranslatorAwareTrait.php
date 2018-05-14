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
     * @param \Viserio\Contract\Translation\Translator $translator
     *
     * @return static
     */
    public function setTranslator(TranslatorContract $translator): self
    {
        $this->translator = $translator;

        return $this;
    }
}
