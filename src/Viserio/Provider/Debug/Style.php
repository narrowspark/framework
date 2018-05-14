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

final class Style
{
    public const NARROWSPARK_THEME = [
        'default' => 'color:#ffffff; line-height:normal; font:12px "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace !important; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-word',
        'num' => 'color:#bcd42a',
        'const' => 'color:#4bb1b1;',
        'str' => 'color:#bcd42a',
        'note' => 'color:#ef7c61',
        'ref' => 'color:#a0a0a0',
        'public' => 'color:#ffffff',
        'protected' => 'color:#ffffff',
        'private' => 'color:#ffffff',
        'meta' => 'color:#ffffff',
        'key' => 'color:#bcd42a',
        'index' => 'color:#ef7c61',
    ];

    private function __construct()
    {
    }
}
