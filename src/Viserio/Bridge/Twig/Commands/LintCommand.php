<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Twig_Environment;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;

class LintCommand extends Command implements RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    use ConfigurationTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:lint';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a template and outputs encountered errors';

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
            'paths',
            'engines' => [
                'twig' => [
                    'options' => [
                        'debug',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get a finder instance of Twig files in the specified directories.
     *
     * @param array $paths Paths to search for files in.
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public function getFinder(array $paths): Finder
    {
        $finder = (empty($this->finder)) ? Finder::create() : $this->finder;

        return $finder->files()->in($paths)->name('*.' . $this->options['engines']['twig']['extension']);
    }

    /**
     * Set the finder used to search for Twig files.
     *
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    public function setFinder(Finder $finder): void
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $container = $this->getContainer();

        $this->configureOptions($container);

        if ($container->has(Twig_Environment::class)) {
            $this->error('The Twig environment needs to be set.');

            return 1;
        }

        $twig      = $container->get(Twig_Environment::class);
        $filenames = $this->input->getArgument('filename');
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
    {
        return [
            [
                'format',
                InputArgument::IS_ARRAY,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The output format (text or json)',
                'text',
            ],
        ];
    }
}
