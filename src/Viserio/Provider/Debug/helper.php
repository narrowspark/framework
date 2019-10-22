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

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Viserio\Provider\Debug\HtmlDumper;
use Viserio\Provider\Debug\Style;

/**
 * Register Viserio's dumper.
 */
VarDumper::setHandler(static function ($value): void {
    $dumper = (new HtmlDumper())->addTheme('dark', Style::NARROWSPARK_THEME);

    if (\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
        $dumper = new CliDumper();
    }

    $dumper->dump((new VarCloner())->cloneVar($value));
});
