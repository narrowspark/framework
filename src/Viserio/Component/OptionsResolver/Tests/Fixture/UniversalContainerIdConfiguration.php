<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use ArrayIterator;
use ArrayObject;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class UniversalContainerIdConfiguration implements RequiresComponentConfigIdContract, ProvidesDefaultOptionsContract, RequiresMandatoryOptionsContract
{
    public const TYPE_ARRAY_ITERATOR = 0;
    public const TYPE_ARRAY_OBJECT   = 1;
    public const TYPE_ARRAY_ARRAY    = 2;
    public const TYPE_ONLY_ITERATOR  = 3;

    /**
     * @var array
     */
    private static $dimensions = [
        'doctrine',
        'universal',
    ];

    /**
     * @var array
     */
    private static $getMandatoryOptions = [
        'params' => ['user', 'dbname'],
        'driverClass',
    ];

    /**
     * @var array
     */
    private static $getDefaultOptions = [
        'params' => [
            'host' => 'awesomehost',
            'port' => '4444',
        ],
    ];

    /**
     * @var int
     */
    private static $type;

    public function __construct(int $type)
    {
        self::$type = $type;
    }

    public static function getDimensions(): iterable
    {
        return self::getData('dimensions');
    }

    public static function getMandatoryOptions(): iterable
    {
        return self::getData('getMandatoryOptions');
    }

    public static function getDefaultOptions(): iterable
    {
        return self::getData('getDefaultOptions');
    }

    private static function getData($name)
    {
        switch (self::$type) {
            case self::TYPE_ARRAY_ITERATOR:
                return new ArrayIterator(self::$$name);

                break;
            case self::TYPE_ARRAY_OBJECT:
                return new ArrayObject(self::$$name);

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
