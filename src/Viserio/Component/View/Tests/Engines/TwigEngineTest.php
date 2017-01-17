<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Engines;

use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Engines\TwigEngine;

class TwigEngineTest extends TestCase
{
    public function testGet()
    {
        $engine = new TwigEngine([
            'template' => [
                'default' => __DIR__ . '/../Fixture/',
                'paths'   => [
                    __DIR__,
                ],
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
