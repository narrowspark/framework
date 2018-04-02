<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test;

use Composer\Composer;
use Composer\IO\NullIO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Viserio\Component\Discovery\Configurator;
use Viserio\Component\Discovery\Configurator\AbstractConfigurator;

class ConfiguratorTest extends TestCase
{
    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\NullIo
     */
    private $nullIo;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer();
        $this->nullIo   = new NullIO();
    }

    public function testAdd(): void
    {
        $configurator = new Configurator($this->composer, $this->nullIo);

        $ref      = new ReflectionClass($configurator);
        $property = $ref->getProperty('configurators');
        $property->setAccessible(true);

        self::assertArrayNotHasKey('mock-configurator', $property->getVReflectionClassalue($configurator));

        $mockConfigurator = $this->getMockForAbstractClass(AbstractConfigurator::class, [$this->composer, $this->nullIo, []]);
        $configurator->add('mock-configurator', get_class($mockConfigurator));

        self::assertArrayHasKey('mock-configurator', $property->getValue($configurator));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Configurator with the name "foo/mock-configurator" already exists.
     */
    public function testAddWithExistingConfiguratorName(): void
    {
        $configurator = new Configurator($this->composer, $this->nullIo);

        $mockConfigurator = $this->getMockForAbstractClass(AbstractConfigurator::class, [$this->composer, $this->nullIo, []]);
        $configurator->add('mock-configurator', get_class($mockConfigurator));
        $configurator->add('mock-configurator', get_class($mockConfigurator));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Configurator class "stdClass" must extend the class "Viserio\Component\Discovery\Configurator\AbstractConfigurator".
     */
    public function testAddWithoutAbstractConfiguratorClass(): void
    {
        $configurator = new Configurator($this->composer, $this->nullIo);

        $configurator->add('foo/mock-configurator', \stdClass::class);
    }
}
