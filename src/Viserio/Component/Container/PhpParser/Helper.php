<?php
declare(strict_types=1);
namespace Viserio\Component\Container\PhpParser;

use Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Viserio\Component\Contract\Container\Exception\RuntimeException;

class Helper
{
    private static $parser;

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Returns PHPParser Node code to string.
     *
     * @param \PhpParser\Node $ast
     *
     * @return string
     */
    public static function prettyAstPrint(Node $ast): string
    {
        return (new Standard())->prettyPrint([$ast]);
    }

    /**
     * @param string $source
     *
     * @return array
     */
    public static function getUsesFromSource(string $source): array
    {
        if (self::$parser === null) {
            self::$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        }

        try {
            $statements = self::$parser->parse($source);
        } catch (Error $error) {
            throw new RuntimeException(\sprintf('Parse error: %s', $error->getMessage()));
        }

        if ($statements === null) {
            return [];
        }

        $statements = \array_filter($statements, function ($v) {
            return $v instanceof Namespace_;
        });

        if (\count($statements) === 0) {
            return [];
        }

        $firstStatement = \reset($statements);
        $uses           = [];

        foreach ($firstStatement->stmts as $statement) {
            if ($statement instanceof Use_ || $statement instanceof GroupUse) {
                $prefix = '';

                if ($statement instanceof GroupUse) {
                    $prefix = $statement->prefix;
                }

                foreach ($statement->uses as $use) {
                    $name  = \implode('\\', $use->name->parts);
                    $alias = $use->alias;

                    if ($alias !== null) {
                        $alias = $alias->name;
                    }

                    if ($prefix !== '') {
                        $name = "{$prefix}\\{$name}";
                    }

                    $uses[$name] = $alias;
                }
            }
        }

        return $uses;
    }
}
