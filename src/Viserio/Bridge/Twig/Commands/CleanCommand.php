<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions;
use Viserio\Component\OptionsResolver\OptionsResolver;

class CleanCommand extends Command implements RequiresConfig, RequiresMandatoryOptions
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:clean';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean the Twig Cache';

    /**
     * Config array.
     *
     * @var array|\ArrayAccess
     */
    protected $options;

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

        if ($this->options === null) {
            $optionsResolver = new OptionsResolver($this);
            $optionsResolver->setContainer($this->container);

            $this->options = $optionsResolver->resolve();
        }

        $files    = $container->get(FilesystemContract::class);
        $cacheDir = $this->options['engines']['twig']['options']['cache'];

        $files->deleteDirectory($cacheDir);

        if ($files->exists($cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');
        } else {
            $this->info('Twig cache cleaned.');
        }
    }
}
