<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Command;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CleanCommand extends Command implements RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:clean';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean the Twig Cache';

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
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
    public function handle()
    {
        $container = $this->getContainer();
        $options   = $this->resolveOptions($container);

        $files    = $container->get(FilesystemContract::class);
        $cacheDir = $options['engines']['twig']['options']['cache'];

        $files->deleteDirectory($cacheDir);

        if ($files->exists($cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');
        } else {
            $this->info('Twig cache cleaned.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }
}
