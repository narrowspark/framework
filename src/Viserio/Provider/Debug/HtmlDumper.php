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

namespace Viserio\Provider\Debug;

use Symfony\Component\VarDumper\Dumper\HtmlDumper as SymfonyHtmlDumper;

class HtmlDumper extends SymfonyHtmlDumper
{
    /**
     * Add a new theme to the html dumper.
     *
     * @param string $name
     * @param array  $options
     *
     * @return static
     */
    public function addTheme(string $name, array $options): HtmlDumper
    {
        self::$themes[$name] = $options;

        return $this;
    }
}
