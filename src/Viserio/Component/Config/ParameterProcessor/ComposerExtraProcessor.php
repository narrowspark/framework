<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Config\ParameterProcessor;

use RuntimeException;

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
     * @param string $dirPath
     * @param string $composerJsonName
     */
    public function __construct(string $dirPath, string $composerJsonName = 'composer.json')
    {
        $this->composerJsonPath = \rtrim($dirPath, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $composerJsonName;
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
            throw new RuntimeException(\sprintf('%s in [%s] file.', \json_last_error_msg(), $this->composerJsonPath), \json_last_error());
        }

        $parameterKey = $this->parseParameter($data);

        $newValue = $json['extra'][$parameterKey] ?? null;

        if ($newValue === null) {
            return $data;
        }

        return $this->replaceData($data, $parameterKey, (string) $newValue);
    }
}
