<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Extensions\ConfigExtension;

class ConfigExtensionTest extends TestCase
{
    public function testGetName()
    {
        $this->assertEquals('Viserio_Bridge_Twig_Extension_Code', (new ConfigExtension())->getName());
    }
}
