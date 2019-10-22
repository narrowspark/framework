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

namespace Viserio\Contract\Translation\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Translation\Traits\TranslatorAwareTrait;
use Viserio\Contract\Translation\Translator as TranslatorContract;

/**
 * @internal
 *
 * @small
 */
final class TranslationAwareTraitTest extends MockeryTestCase
{
    use TranslatorAwareTrait;

    public function testGetAndSetTranslator(): void
    {
        $this->setTranslator(\Mockery::mock(TranslatorContract::class));

        self::assertNotNull($this->translator);
    }
}
