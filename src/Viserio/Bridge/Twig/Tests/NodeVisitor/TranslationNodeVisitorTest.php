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

namespace Viserio\Provider\Twig\Tests\NodeVisitor;

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

        $this->loaderMock = \Mockery::mock(LoaderInterface::class);
    }

    /**
     * @dataProvider provideMessagesExtractionCases
     *
     * @param \Twig\Node\Node $node
     * @param array           $expectedMessages
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

    public function provideMessagesExtractionCases(): iterable
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

    /**
     * @param string      $message
     * @param null|string $domain
     * @param null|array  $arguments
     *
     * @return \Twig\Node\Expression\FilterExpression
     */
    private static function getTransFilter(
        string $message,
        string $domain = null,
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

    /**
     * @param string      $message
     * @param null|string $domain
     *
     * @return \Viserio\Bridge\Twig\Node\TransNode
     */
    private static function getTransTag(string $message, ?string $domain = null): TransNode
    {
        return new TransNode(
            new BodyNode([], ['data' => $message]),
            $domain ? new ConstantExpression($domain, 0) : null
        );
    }
}
