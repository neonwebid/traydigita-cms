<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions\Parser;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class WhileParser extends AbstractTokenParser
{
    public function parse(Token $token): WhileNode
    {
        // create while loop
        $lineno = $token->getLine();
        $expr = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWhileEnd']);
        $tests = [$expr];
        if ($stream->next()->getValue() !== 'endwhile') {
            throw new SyntaxError(
                sprintf(
                    'Syntax error: Unclosed while statement in %s',
                    $lineno
                )
            );
        }
        $stream->expect(Token::BLOCK_END_TYPE);
        return new WhileNode(
            new Node($tests),
            $body,
            $lineno,
            $this->getTag()
        );
    }

    public function getTag(): string
    {
        return 'while';
    }

    public function decideWhileEnd(Token $token): bool
    {
        return $token->test('endwhile');
    }
}
