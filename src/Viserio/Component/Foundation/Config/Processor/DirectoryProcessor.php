<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Config\Processor;

use Psr\Container\ContainerInterface;
use Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

final class DirectoryProcessor extends AbstractParameterProcessor implements
    RequiresMandatoryOptionsContract,
    RequiresComponentConfigContract,
    RequiresValidatedConfigContract
{
    use OptionsResolverTrait;
    use ContainerAwareTrait;

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    public function __construct($config, ContainerInterface $container)
    {
        $this->resolvedOptions = self::resolveOptions($config);
        $this->container       = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'config', 'processor', self::getReferenceKeyword()];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return ['mapper'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'mapper' => ['array'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'directory';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        $parameterKey      = $this->parseParameter($data);
        $parameterKeyValue = $this->resolvedOptions['mapper'][$parameterKey] ?? null;

        if ($parameterKeyValue === null) {
            return $data;
        }

        return $this->replaceData(
            $data,
            $parameterKey,
            $this->container->has($parameterKeyValue[0]) ? $this->container->get($parameterKeyValue[0])->{$parameterKeyValue[1]}() : null
        );
    }
}
