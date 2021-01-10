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

namespace Viserio\Contract\Translation\Tests\Traits;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Translation\Traits\TranslatorAwareTrait;
use Viserio\Contract\Translation\Translator as TranslatorContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class TranslationAwareTraitTest extends MockeryTestCase
{
    use TranslatorAwareTrait;

    public function testGetAndSetTranslator(): void
    {
        $this->setTranslator(Mockery::mock(TranslatorContract::class));

        self::assertNotNull($this->translator);
    }
}
