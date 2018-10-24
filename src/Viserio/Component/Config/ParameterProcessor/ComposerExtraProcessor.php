<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

class ComposerExtraProcessor extends AbstractParameterProcessor
{
    /**
     * Path to the composer.json.
     *
     * @var string
     */
    private $composerJsonPath;

    /**
     * Create a new ComposerExtraProcessor instance.
     *
     * @param string $composerJsonPath
     */
    public function __construct(string $composerJsonPath)
    {
        $this->composerJsonPath = $composerJsonPath;
    }

    /**
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'composer-extra';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        $json = \json_decode(\trim(\file_get_contents($this->composerJsonPath)), true);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \RuntimeException(\json_last_error_msg() . '.', \json_last_error());
        }

        $parameterKey = $this->parseParameter($parameter);

        return $json['extra'][$parameterKey] ?? null;
    }
}
