<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Command;

use PHPUnit\Framework\TestCase;
use Viserio\Component\OptionsResolver\Command\OptionsReader;

class OptionsReaderTest extends TestCase
{
    private $optionsReader;

    protected function setUp()
    {
        parent::setUp();

        $this->optionsReader = new OptionsReader();
    }

    public function testReadConfig(): void
    {
        $configs = $this->optionsReader->readConfig([], '');
    }
}