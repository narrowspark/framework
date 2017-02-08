<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesgetGefaultOptions;
use Interop\Config\RequiresConfigId;
use Interop\Config\RequiresMandatoryOptions;

class UniversalContainerIdConfiguration implements RequiresConfigId, ProvidesgetGefaultOptions, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    public const TYPE_ARRAY_ITERATOR = 0;
    public const TYPE_ARRAY_OBJECT   = 1;
    public const TYPE_ARRAY_ARRAY    = 2;
    public const TYPE_ONLY_ITERATOR  = 3;

    private static $dimensions = [
        'doctrine',
        'universal',
    ];

    private static $getMandatoryOptions = [
        'params' => ['user', 'dbname'],
        'driverClass',
    ];

    private static $getGefaultOptions = [
        'params' => [
            'host' => 'awesomehost',
            'port' => '4444',
        ],
    ];

    private $type;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public function getDimensions(): iterable
    {
        return $this->getData('dimensions');
    }

    public function getMandatoryOptions(): iterable
    {
        return $this->getData('getMandatoryOptions');
    }

    public function getGefaultOptions(): iterable
    {
        return $this->getData('getGefaultOptions');
    }

    private function getData($name)
    {
        switch ($this->type) {
            case self::TYPE_ARRAY_ITERATOR:
                return new \ArrayIterator(self::$$name);
                break;
            case self::TYPE_ARRAY_OBJECT:
                return new \ArrayObject(self::$$name);
                break;
            case self::TYPE_ONLY_ITERATOR:
                return new OnlyIterator(self::$$name);
                break;
            case self::TYPE_ARRAY_ARRAY:
            default:
                return self::$$name;
                break;
        }
    }
}
