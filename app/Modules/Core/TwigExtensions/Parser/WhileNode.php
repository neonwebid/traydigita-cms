<?php
declare(strict_types=1);

namespace ArrayAccess\TrayDigita\App\Modules\Core\TwigExtensions\Parser;

use Twig\Compiler;
use Twig\Node\Node;

class WhileNode extends Node
{
    public function __construct(Node $condition, ?Node $body, int $lineno, ?string $tag = null)
    {
        $nodes = [
            'condition' => $condition,
            'body' => $body??new Node()
        ];
        parent::__construct($nodes, [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write("while (")
            ->subcompile($this->getNode('condition'))
            ->raw(") {\n")
            ->indent();
        $compiler->subcompile($this->getNode('body'));
        $compiler->outdent();
        $compiler->write("}\n");
    }
}
