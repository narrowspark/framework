<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Engines;

use PHPUnit\Framework\TestCase;
use Viserio\View\Engines\PlatesEngine;

class PlatesEngineTest extends TestCase
{
    public function testGet()
    {
        $engine = new PlatesEngine([
            'template' => [
                'default' => __DIR__ . '/../Fixture/',
            ]
        ]);

        $template = $engine->get(['name' => 'plates.php']);

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
