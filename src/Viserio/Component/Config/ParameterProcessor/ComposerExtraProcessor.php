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
    public function process(string $data)
    {
        $json = \json_decode(\trim(\file_get_contents($this->composerJsonPath)), true);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \RuntimeException(\sprintf('%s in [%s] file.', \json_last_error_msg(), $this->composerJsonPath), \json_last_error());
        }

        $parameterKey = $this->parseParameter($data);

        return $this->replaceData($data, $parameterKey, $json['extra'][$parameterKey] ?? null);
    }
}
