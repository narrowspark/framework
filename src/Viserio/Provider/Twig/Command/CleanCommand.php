<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Command;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CleanCommand extends Command implements RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'twig:clear';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean the Twig Cache';

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
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
        $container = $this->getContainer();
        $options   = self::resolveOptions($container);

        $files    = $container->get(FilesystemContract::class);
        $cacheDir = $options['engines']['twig']['options']['cache'];

        $files->deleteDirectory($cacheDir);

        if ($files->has($cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');

            return 1;
        }

        $this->info('Twig cache cleaned.');

        return 0;
    }
}
