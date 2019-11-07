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

namespace Viserio\Component\Container\PhpParser;

use Exception;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use ReflectionException;
use Viserio\Component\Container\PhpParser\Reflection\PrivatesCaller;

/**
 * @internal
 */
final class PrettyPrinter extends Standard
{
    /**
     * A private caller instance.
     *
     * @var \Viserio\Component\Container\PhpParser\Reflection\PrivatesCaller
     */
    private $privatesCaller;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [])
    {
        parent::__construct(\array_merge(
            $options,
            ['shortArraySyntax' => true]
        ));

        $this->privatesCaller = new PrivatesCaller();

        // print return type double colon right after the bracket "function(): string"
        $this->initializeInsertionMap();
        $this->insertionMap['Stmt_ClassMethod->returnType'] = [')', false, ': ', null];
        $this->insertionMap['Stmt_Function->returnType'] = [')', false, ': ', null];
    }

    /**
     * @param null|\PhpParser\Node|\PhpParser\Node[] $node
     *
     * @return string
     */
    public function prettyPrint($node): string
    {
        if ($node === null) {
            $node = [];
        }

        if ($node instanceof EncapsedStringPart) {
            return 'UNABLE_TO_PRINT_ENCAPSED_STRING';
        }

        if (! \is_array($node)) {
            $node = [$node];
        }

        return parent::prettyPrint($node);
    }

    /**
     * Do not preslash all slashes (parent behavior), but only those:.
     *
     * - followed by "\"
     * - by "'"
     * - or the end of the string
     *
     * Prevents `Vendor\Class` => `Vendor\\Class`.
     *
     * @param string $string
     *
     * @return string
     */
    protected function pSingleQuotedString(string $string): string
    {
        return '\'' . \preg_replace("#'|\\\\(?=[\\\\']|$)#", '\\\\$0', $string) . '\'';
    }

    /**
     * Add space after "use (".
     *
     * @param \PhpParser\Node\Expr\Closure $node
     *
     * @return string
     */
    protected function pExpr_Closure(Closure $node): string
    {
        return \preg_replace('#( use)\(#', '$1 (', parent::pExpr_Closure($node));
    }

    /**
     * Do not add "()" on Expressions.
     *
     * Before: return [['item']];
     * After: yield ['item'];
     *
     * @param \PhpParser\Node\Expr\Yield_ $node
     *
     * @return string
     */
    protected function pExpr_Yield(Yield_ $node): string
    {
        if ($node->value === null) {
            return 'yield';
        }

        $parentNode = $node->getAttribute('parentNode');
        $shouldAddBrackets = $parentNode instanceof Expression;

        return \sprintf(
            '%syield %s%s%s',
            $shouldAddBrackets ? '(' : '',
            $node->key !== null ? $this->p($node->key) . ' => ' : '',
            $this->p($node->value),
            $shouldAddBrackets ? ')' : ''
        );
    }

    /**
     * Print arrays in short [] by default,
     * to prevent manual explicit array shortening.
     *
     * @param \PhpParser\Node\Expr\Array_ $node
     *
     * @return string
     */
    protected function pExpr_Array(Array_ $node): string
    {
        if (! $node->hasAttribute('kind')) {
            $node->setAttribute('kind', Array_::KIND_SHORT);
        }

        return parent::pExpr_Array($node);
    }

    /**
     * Allows PHP 7.3 trailing comma in multiline args.
     *
     * @see printArgs() bellow
     *
     * @param \PhpParser\Node\Expr\FuncCall $node
     *
     * @return string
     */
    protected function pExpr_FuncCall(FuncCall $node): string
    {
        return $this->pCallLhs($node->name) . '(' . $this->printArgs($node) . ')';
    }

    /**
     * Allows PHP 7.3 trailing comma in multiline args.
     *
     * @see printArgs() bellow
     *
     * @param \PhpParser\Node\Expr\MethodCall $node
     *
     * @return string
     */
    protected function pExpr_MethodCall(MethodCall $node): string
    {
        return $this->pDereferenceLhs($node->var)
            . '->'
            . $this->pObjectProperty($node->name)
            . '('
            . $this->printArgs($node)
            . ')';
    }

    /**
     * Allows PHP 7.3 trailing comma in multiline args.
     *
     * @see printArgs() bellow
     *
     * @param \PhpParser\Node\Expr\StaticCall $node
     *
     * @return string
     */
    protected function pExpr_StaticCall(StaticCall $node): string
    {
        return $this->pDereferenceLhs($node->class) . '::'
            . ($node->name instanceof Expr
                ? ($node->name instanceof Variable
                    ? $this->p($node->name)
                    : '{' . $this->p($node->name) . '}')
                : $node->name)
            . '(' . $this->printArgs($node) . ')';
    }

    /**
     * Fixes escaping of regular patterns.
     *
     * @param \PhpParser\Node\Scalar\String_ $node
     *
     * @throws Exception
     *
     * @return string
     */
    protected function pScalar_String(String_ $node): string
    {
        $kind = $node->getAttribute('kind', String_::KIND_SINGLE_QUOTED);

        if ($kind === String_::KIND_DOUBLE_QUOTED && $node->getAttribute('is_regular_pattern') === true) {
            return '"' . $node->value . '"';
        }

        return parent::pScalar_String($node);
    }

    /**
     * "...$params) : ReturnType"
     * ↓
     * "...$params): ReturnType".
     *
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     *
     * @return string
     */
    protected function pStmt_ClassMethod(ClassMethod $node): string
    {
        return $this->pModifiers($node->flags)
            . 'function ' . ($node->byRef ? '&' : '') . $node->name
            . '(' . $this->pCommaSeparated($node->params) . ')'
            . ($node->returnType !== null ? ': ' . $this->p($node->returnType) : '')
            . ($node->stmts !== null ? $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
    }

    /**
     * Overridden to fix indentation problem with tabs.
     *
     * If the original source code uses tabs, then the tokenizer
     * will see this as "1" indent level, and will indent new lines
     * with just 1 space. By changing 1 indent to 4, we effectively
     * "correct" this problem when printing.
     *
     * For code that is even further indented (e.g. 8 spaces),
     * the printer uses the first indentation (here corrected
     * from 1 space to 4) and already (without needing any other
     * changes) adds 4 spaces onto that. This is why we don't
     * also need to handle indent levels of 5, 9, etc: these
     * do not occur (at least in the code we generate);
     *
     * @param int $level
     */
    protected function setIndentLevel(int $level): void
    {
        if (1 === $level) {
            $level = 4;
        }

        parent::setIndentLevel($level);
    }

    /**
     * Allows PHP 7.3 trailing comma in multiline args.
     *
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     *
     * @throws ReflectionException
     *
     * @return string
     */
    private function printArgs($node): string
    {
        return PrivatesCaller::callPrivateMethod(
            $this,
            'pMaybeMultiline',
            $node->args,
            $node->getAttribute('trailingComma', false)
        );
    }
}
