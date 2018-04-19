<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Discovery\Configurator;

use Narrowspark\Discovery\Common\Configurator\AbstractConfigurator;
use Narrowspark\Discovery\Common\Contract\Package as PackageContract;
use Narrowspark\Discovery\Common\Exception\InvalidArgumentException;
use Viserio\Component\Foundation\Project\GenerateFolderStructureAndFiles;

class ProjectConfigurator extends AbstractConfigurator
{
    /**
     * @var string
     */
    private const FULL_PROJECT = 'full';

    /**
     * @var string
     */
    private const HTTP_PROJECT = 'http';

    /**
     * @var string
     */
    private const CONSOLE_PROJECT = 'console';

    /**
     * This should be only used if this class is tested.
     *
     * @internal
     *
     * @var bool
     */
    public static $isTest = false;

    /**
     * @var string
     */
    private static $question = '    Please choose you project type.
    [<comment>f</comment>] Full Stack framework
    [<comment>h</comment>] Http framework
    [<comment>c</comment>] Console framework
    (defaults to <comment>f</comment>): ';

    /**
     * {@inheritdoc}
     */
    public function configure(PackageContract $package): void
    {
        $this->write('Creating project directories and files');

        $answer = $this->io->askAndValidate(
            self::$question,
            [$this, 'validateProjectQuestionAnswerValue'],
            null,
            'f'
        );
        $mapping = [
            'f' => self::FULL_PROJECT,
            'c' => self::CONSOLE_PROJECT,
            'h' => self::HTTP_PROJECT,
        ];

        GenerateFolderStructureAndFiles::create($this->options, $mapping[$answer], $this->io);
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(PackageContract $package): void
    {
        $this->write('Project cant be unconfigure');
    }

    /**
     * Validate given input answer.
     *
     * @param null|string $value
     *
     * @throws \Narrowspark\Discovery\Common\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function validateProjectQuestionAnswerValue(?string $value): string
    {
        if ($value === null) {
            return 'f';
        }

        $value = \mb_strtolower($value[0]);

        if (! \in_array($value, ['f', 'h', 'c'], true)) {
            throw new InvalidArgumentException('Invalid choice');
        }

        return $value;
    }
}
