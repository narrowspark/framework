<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests;

use ArrayIterator;
use ArrayObject;
use Interop\Container\ContainerInterface;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\OptionsResolver\ComponentOptionsResolver;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionDefaultOptionsMandatoryContainetIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionMandatoryContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\ConnectionMandatoryRecursiveContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\FlexibleConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\PackageDefaultAndMandatoryOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\PackageDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\PlainConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixtures\UniversalContainerIdConfiguration;

class ComponentOptionsResolverTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException
     * @expectedExceptionMessage position is "doctrine"
     */
    public function testOptionsResolverThrowsInvalidArgumentExceptionIfConfigIsNotAnArray()
    {
        $this->getComponentOptionsResolver(new ConnectionConfiguration(), ['doctrine' => new stdClass()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The factory
     */
    public function testOptionsThrowsInvalidArgumentExIfConfigIdIsProvidedButRequiresConfigIdIsNotImplemented()
    {
        $this->getComponentOptionsResolver(new ConnectionConfiguration(), ['doctrine' => []], 'configId');
    }

    /**
     * @dataProvider providerConfig
     *
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     * @expectedExceptionMessage The configuration
     *
     * @param mixed $config
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfConfigIdIsMissingWithRequiresConfigId($config)
    {
        $this->getComponentOptionsResolver(new ConnectionContainerIdConfiguration(), $config);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     * @expectedExceptionMessage No options set for configuration "doctrine.connection"
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfNoVendorConfigIsAvailable()
    {
        $this->getComponentOptionsResolver(new ConnectionConfiguration(), ['doctrine' => []]);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     * @expectedExceptionMessage No options set for configuration "doctrine.connection"
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfNoPackageOptionIsAvailable()
    {
        $this->getComponentOptionsResolver(new ConnectionConfiguration(), ['doctrine' => ['connection' => null]]);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     * @expectedExceptionMessage No options set for configuration "doctrine.connection.orm_default"
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfNoContainerIdOptionIsAvailable()
    {
        $this->getComponentOptionsResolver(
            new ConnectionContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => null]]],
            'orm_default'
        );
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     * @expectedExceptionMessage No options set for configuration "one.two.three.four"
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfDimensionIsNotAvailable()
    {
        $this->getComponentOptionsResolver(
            new FlexibleConfiguration(),
            ['one' => ['two' => ['three' => ['invalid' => ['dimension']]]]]
        );
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     * @expectedExceptionMessage No options set for configuration "vendor"
     */
    public function testOptionsThrowsExceptionIfMandatoryOptionsWithDefaultOptionsSetAndNoConfigurationIsSet()
    {
        $this->getComponentOptionsResolver(
            new PackageDefaultAndMandatoryOptionsConfiguration(),
            []
        );
    }

    /**
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException
     * @expectedExceptionMessage Configuration must either be of
     */
    public function testOptionsThrowsUnexpectedValueExceptionIfRetrievedOptionsNotAnArrayOrArrayAccess()
    {
        $this->getComponentOptionsResolver(
            new ConnectionContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => new \stdClass()]]],
            'orm_default'
        );
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithContainerId($config)
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertArrayHasKey('driverClass', $options);
        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsReturnsData($config)
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionConfiguration(),
            $config
        );

        self::assertArrayHasKey('orm_default', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithFlexibleDimensions($config)
    {
        $options = $this->getComponentOptionsResolver(
            new FlexibleConfiguration(),
            $config
        );

        self::assertArrayHasKey('name', $options);
        self::assertArrayHasKey('class', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithNoDimensions($config): void
    {
        $options = $this->getComponentOptionsResolver(
            new PlainConfiguration(),
            $config
        );

        self::assertArrayHasKey('doctrine', $options);
        self::assertArrayHasKey('one', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithDefaultOptions($config): void
    {
        $stub = new ConnectionDefaultOptionsMandatoryContainetIdConfiguration();

        unset($config['doctrine']['connection']['orm_default']['params']['host'], $config['doctrine']['connection']['orm_default']['params']['port']);

        $options = $options = $this->getComponentOptionsResolver(
            $stub,
            $config,
            'orm_default'
        );
        $defaultOptions = $stub->getDefaultOptions();

        self::assertCount(2, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame($options['params']['host'], $defaultOptions['params']['host']);
        self::assertSame($options['params']['port'], $defaultOptions['params']['port']);
        self::assertSame(
            $options['params']['user'],
            $config['doctrine']['connection']['orm_default']['params']['user']
        );

        $config = $this->getTestConfig();

        // remove main index key
        unset($config['doctrine']['connection']['orm_default']['params']);

        $options = $options = $this->getComponentOptionsResolver(
            $stub,
            $config,
            'orm_default'
        );

        self::assertCount(2, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame($options['params']['host'], $defaultOptions['params']['host']);
        self::assertSame($options['params']['port'], $defaultOptions['params']['port']);
    }

    public function testOptionsReturnsPackageDataWithDefaultOptionsIfNoConfigurationIsSet(): void
    {
        $expected = [
            'minLength' => 2,
            'maxLength' => 10,
        ];
        $options = $this->getComponentOptionsResolver(
            new PackageDefaultOptionsConfiguration(),
            []
        );

        self::assertCount(2, $options);
        self::assertSame($expected, $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsThatDefaultOptionsNotOverrideProvidedOptions($config): void
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionDefaultOptionsMandatoryContainetIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertCount(2, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame(
            $options['params']['host'],
            $config['doctrine']['connection']['orm_default']['params']['host']
        );
        self::assertSame(
            $options['params']['port'],
            $config['doctrine']['connection']['orm_default']['params']['port']
        );
        self::assertSame(
            $options['params']['user'],
            $config['doctrine']['connection']['orm_default']['params']['user']
        );
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsChecksMandatoryOptions($config): void
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryConfiguration(),
            $config
        );

        self::assertCount(1, $options);
        self::assertArrayHasKey('orm_default', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsChecksMandatoryOptionsWithContainerId($config): void
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertCount(2, $options);
        self::assertArrayHasKey('driverClass', $options);
        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     * @expectedExceptionMessage Mandatory option "params"
     *
     * @param mixed $config
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfMandatoryOptionIsMissing($config): void
    {
        unset($config['doctrine']['connection']['orm_default']['params']);

        $this->getComponentOptionsResolver(
            new ConnectionMandatoryContainerIdConfiguration(),
            $config,
            'orm_default'
        );
    }

    /**
     * @dataProvider providerConfig
     *
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     * @expectedExceptionMessage Mandatory option "dbname"
     *
     * @param mixed $config
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfMandatoryOptionRecursiveIsMissing($config): void
    {
        unset($config['doctrine']['connection']['orm_default']['params']['dbname']);

        $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveContainerIdConfiguration(),
            $config,
            'orm_default'
        );
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsWithRecursiveMandatoryOptionCheck($config): void
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @param mixed $config
     */
    public function testOptionsWithRecursiveArrayIteratorMandatoryOptionCheck($config): void
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider providerConfig
     *
     * @expectedException \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     * @expectedExceptionMessage Mandatory option "params"
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfOptionsAreEmpty(): void
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => []]]],
            'orm_default'
        );
    }

    public function testEmptyArrayAccessWithDefaultOptions()
    {
        $options = $this->getComponentOptionsResolver(
            new ConnectionDefaultOptionsConfiguration(),
            new ArrayIterator([])
        );

        self::assertCount(1, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame(
            $options['params']['host'],
            'awesomehost'
        );
        self::assertSame(
            $options['params']['port'],
            '4444'
        );
    }

    /**
     * @dataProvider providerConfigObjects
     *
     * @param mixed $config
     * @param mixed $type
     */
    public function testOptionsWithObjects($config, $type): void
    {
        $options = $this->getComponentOptionsResolver(
            new UniversalContainerIdConfiguration($type),
            $config,
            'orm_default'
        );

        self::assertCount(2, $options);
        self::assertArrayHasKey('driverClass', $options);
        self::assertArrayHasKey('params', $options);

        $driverClass = 'Doctrine\DBAL\Driver\PDOMySql\Driver';
        $host        = 'localhost';
        $dbname      = 'database';
        $user        = 'username';
        $password    = 'password';
        $port        = '4444';

        if ($type !== UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR) {
            $driverClass = $config['doctrine']['connection']['orm_default']['driverClass'];
            $host        = $config['doctrine']['connection']['orm_default']['params']['host'];
            $dbname      = $config['doctrine']['connection']['orm_default']['params']['dbname'];
            $user        = $config['doctrine']['connection']['orm_default']['params']['user'];
            $password    = $config['doctrine']['connection']['orm_default']['params']['password'];
        }

        self::assertSame($options['driverClass'], $driverClass);
        self::assertSame($options['params']['host'], $host);
        self::assertSame($options['params']['port'], $port);
        self::assertSame($options['params']['dbname'], $dbname);
        self::assertSame($options['params']['user'], $user);
        self::assertSame($options['params']['password'], $password);
    }

    public function testDataResolverWithRepositoryContract()
    {
        $defaultConfig = [
            // package name
            'connection' => [
                // container id
                'orm_default' => [
                    // mandatory params
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                    'params'      => [
                        'host'     => 'localhost',
                        'port'     => '3306',
                        'user'     => 'username',
                        'password' => 'password',
                        'dbname'   => 'database',
                    ],
                ],
            ],
        ];
        $container  = $this->mock(ContainerInterface::class);
        $config     = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('doctrine')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('doctrine')
            ->andReturn($defaultConfig);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $container,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    public function testDataResolverWithConfig()
    {
        $container  = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(false);
        $container->shouldReceive('has')
            ->with('config')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('config')
            ->andReturn($this->getTestConfig());

        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $container,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    public function testDataResolverWithOptions()
    {
        $container  = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(false);
        $container->shouldReceive('has')
            ->with('config')
            ->andReturn(false);
        $container->shouldReceive('has')
            ->with('options')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('options')
            ->andReturn($this->getTestConfig());

        $options = $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $container,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No configuration found.
     */
    public function testDataResolverThrowException()
    {
        $this->getComponentOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            new stdClass()
        );
    }

    public function providerConfig(): array
    {
        return [
            [$this->getTestConfig()],
            [new ArrayObject($this->getTestConfig())],
            [new ArrayIterator($this->getTestConfig())],
        ];
    }

    public function providerConfigObjects(): array
    {
        return [
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ARRAY_ARRAY],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ARRAY],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ARRAY],
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ARRAY_OBJECT],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_OBJECT],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_OBJECT],
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ARRAY_ITERATOR],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ITERATOR],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ITERATOR],
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR],
        ];
    }

    /**
     * Returns test config.
     *
     * @return array
     */
    private function getTestConfig(): array
    {
        return require __DIR__ . '/Fixtures/testing.config.php';
    }

    private function getComponentOptionsResolver($class, $data, string $id = null)
    {
        return (new ComponentOptionsResolver())->configure($class, $data)->resolve($id);
    }
}
