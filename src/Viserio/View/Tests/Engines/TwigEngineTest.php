<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Engines;

use PHPUnit\Framework\TestCase;
use Viserio\View\Engines\TwigEngine;

class TwigEngineTest extends TestCase
{
    public function testSimpleGet()
    {
        $engine = new TwigEngine([
            'template' => [
                'default' => __DIR__ . '/../Fixture/',
            ],
        ]);

        $template = $engine->get(['name' => 'twightml.twig.html']);

        static::assertSame(trim('<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <link rel="stylesheet" href="">
</head>
<body>
    hallo
</body>
</html>'), trim($template));
    }
}
