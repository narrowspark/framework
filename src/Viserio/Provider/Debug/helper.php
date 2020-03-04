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
