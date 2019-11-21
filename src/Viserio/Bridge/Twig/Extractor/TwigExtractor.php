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

namespace Viserio\Bridge\Twig\Extractor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Translation\Extractor\AbstractFileExtractor;
use Viserio\Contract\Translation\Exception\RuntimeException;

class TwigExtractor extends AbstractFileExtractor
{
    /**
     * Default domain for found messages.
     *
     * @var string
     */
    protected $defaultDomain = 'messages';

    /**
     * A Twig Environment instance.
     *
     * @var \Twig\Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource): array
    {
        $messages = [];

        foreach ($this->extractFiles($resource) as $file) {
            $fileContent = \file_get_contents($file);

            if ($fileContent === false) {
                throw new RuntimeException(\sprintf('A failure happened on reading [%s].', $file));
            }

            try {
                foreach ($this->extractTemplate($fileContent) as $k => $v) {
                    $messages[$k] = $v;
                }
            } catch (Error $exception) {
                $exception->setSourceContext(new Source('', \basename($file), (string) \realpath($file)));

                throw $exception;
            }
        }

        return $messages;
    }

    /**
     * Extract translations from template string.
     *
     * @param string $template
     *
     * @throws \Twig\Error\SyntaxError
     *
     * @return array
     */
    protected function extractTemplate(string $template): array
    {
        /** @var \Viserio\Bridge\Twig\Extension\TranslatorExtension $extension */
        $extension = $this->twig->getExtension(TranslatorExtension::class);
        /** @var \Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor $visitor */
        $visitor = $extension->getTranslationNodeVisitor();
        $visitor->enable();

        $this->twig->parse($this->twig->tokenize(new Source($template, '')));

        $messages = [];

        foreach ($visitor->getMessages() as $message) {
            $messages[$message[1] ?? $this->defaultDomain][\trim($message[0])] = $this->prefix . \trim($message[0]);
        }

        $visitor->disable();

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeExtracted(string $file): bool
    {
        return $this->isFile($file) && $this->isTwigFile($file);
    }

    /**
     * @param array|string $directories Files, a file or a directory
     *
     * @return array files to be extracted
     */
    protected function extractFromDirectory($directories): array
    {
        $files = [];

        foreach ((array) $directories as $directory) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($this->isTwigFile($file->getPathname())) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Check if file is a php file.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isTwigFile(string $file): bool
    {
        return \pathinfo($file, \PATHINFO_EXTENSION) === 'twig';
    }
}
