<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extractor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Translation\Extractor\AbstractFileExtractor;

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
            try {
                $messages = \array_merge($messages, $this->extractTemplate(\file_get_contents($file)));
            } catch (Error $exception) {
                $exception->setSourceContext(new Source('', \basename($file), \realpath($file)));

                throw $exception;
            }
        }

        return $messages;
    }

    /**
     * @param string $template
     *
     * @throws \Twig_Error_Syntax
     *
     * @return array
     */
    protected function extractTemplate(string $template): array
    {
        // @var \Twig\NodeVisitor\NodeVisitorInterface
        $visitor = $this->twig->getExtension(TranslatorExtension::class)->getTranslationNodeVisitor();
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
     * @param array|string $directory
     *
     * @return array
     */
    protected function extractFromDirectory($directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files    = [];

        foreach ($iterator as $file) {
            if ($this->isTwigFile($file->getPathname())) {
                $files[] = $file->getPathname();
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
