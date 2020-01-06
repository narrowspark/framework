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
        $json = \json_decode(\trim(\file_get_contents($this->composerJsonPath)), true, 512, \JSON_THROW_ON_ERROR);

        $parameterKey = $this->parseParameter($data);

        $newValue = $json['extra'][$parameterKey] ?? null;

        if ($newValue === null) {
            return $data;
        }

        return $this->replaceData($data, $parameterKey, (string) $newValue);
    }
}
