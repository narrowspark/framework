<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Provider\Twig\Tests\NodeVisitor;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\Node\BodyNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Viserio\Bridge\Twig\Node\TransNode;
use Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class TranslationNodeVisitorTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Twig\Loader\LoaderInterface */
    private $loaderMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loaderMock = Mockery::mock(LoaderInterface::class);
    }

    /**
     * @dataProvider provideMessagesExtractionCases
     */
    public function testMessagesExtraction(Node $node, array $expectedMessages): void
    {
        $env = new Environment($this->loaderMock, ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $visitor = new TranslationNodeVisitor();
        $visitor->enable();
        $visitor->enterNode($node, $env);
        $visitor->leaveNode($node, $env);

        self::assertEquals($expectedMessages, $visitor->getMessages());
    }

    public function testMessageExtractionWithInvalidDomainNode(): void
    {
        $message = 'new key';
        $node = new FilterExpression(
            new ConstantExpression($message, 0),
            new ConstantExpression('trans', 0),
            new Node([
                new ArrayExpression([], 0),
                new NameExpression('variable', 0),
            ]),
            0
        );

        $this->testMessagesExtraction($node, [[$message, '_undefined']]);
    }

    public static function provideMessagesExtractionCases(): iterable
    {
        $message = 'new key';
        $domain = 'domain';

        return [
            [self::getTransFilter($message), [[$message, null]]],
            [self::getTransTag($message), [[$message, null]]],
            [self::getTransFilter($message, $domain), [[$message, $domain]]],
            [self::getTransTag($message, $domain), [[$message, $domain]]],
        ];
    }

    private static function getTransFilter(
        string $message,
        ?string $domain = null,
        ?array $arguments = null
    ): FilterExpression {
        if (! $arguments) {
            $arguments = $domain ? [
                new ArrayExpression([], 0),
                new ConstantExpression($domain, 0),
            ] : [];
        }

        return new FilterExpression(
            new ConstantExpression($message, 0),
            new ConstantExpression('trans', 0),
            new Node($arguments),
            0
        );
    }

    private static function getTransTag(string $message, ?string $domain = null): TransNode
    {
        return new TransNode(
            new BodyNode([], ['data' => $message]),
            $domain ? new ConstantExpression($domain, 0) : null
        );
    }
}
