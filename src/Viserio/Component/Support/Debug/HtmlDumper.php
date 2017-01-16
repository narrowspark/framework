<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Debug;

use Symfony\Component\VarDumper\Dumper\HtmlDumper as SymfonyHtmlDumper;

class HtmlDumper extends SymfonyHtmlDumper
{
    /**
     * Colour definitions for output.
     *
     * @var array
     */
    protected $styles = [
        'default'   => 'color:#FFFFFF; line-height:normal; font:12px "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace !important; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-word',
        'num'       => 'color:#BCD42A',
        'const'     => 'color: #4bb1b1;',
        'str'       => 'color:#BCD42A',
        'note'      => 'color:#ef7c61',
        'ref'       => 'color:#A0A0A0',
        'public'    => 'color:#FFFFFF',
        'protected' => 'color:#FFFFFF',
        'private'   => 'color:#FFFFFF',
        'meta'      => 'color:#FFFFFF',
        'key'       => 'color:#BCD42A',
        'index'     => 'color:#ef7c61',
    ];
}
