<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Command;

use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CleanCommand extends AbstractCommand implements RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * The default command name.
     *
     * @var string
     */
    protected static $defaultName = 'twig:clear';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean the Twig Cache';

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Create a new CleanCommand instance.
     *
     * @param array|\ArrayAccess $config
     */
    public function __construct($config)
    {
        parent::__construct();

        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'engines' => [
                'twig' => [
                    'options' => [
                        'cache',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $cacheDir = $this->resolvedOptions['engines']['twig']['options']['cache'];

        (new Filesystem())->remove($cacheDir);

        @\rmdir($cacheDir);

        if (\is_dir($cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');

            return 1;
        }

        $this->info('Twig cache cleaned.');

        return 0;
    }
}
