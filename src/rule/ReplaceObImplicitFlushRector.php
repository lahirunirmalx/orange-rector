<?php

declare(strict_types=1);

namespace OrangeHRM\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber as NumberNode;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ReplaceObImplicitFlushRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace the integer $enable parameter of ob_implicit_flush() with a boolean value.',
            [
                new CodeSample(
                // Original code with integer parameter
                    <<<'CODE_SAMPLE'
ob_implicit_flush(0);
ob_implicit_flush(1);
CODE_SAMPLE
                    ,
                    // Transformed code with boolean parameter
                    <<<'CODE_SAMPLE'
ob_implicit_flush(false);
ob_implicit_flush(true);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * Define the types of nodes this Rector rule will handle.
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * Refactor the nodes that match the defined types.
     */
    public function refactor(Node $node): ?Node
    {
        // Check if the function call is `ob_implicit_flush`
        if (!$this->isName($node, 'ob_implicit_flush')) {
            return null;
        }

        // Ensure the function has exactly one argument
        if (count($node->args) !== 1) {
            return null;
        }

        // Get the argument and check if it is an integer 0 or 1
        $argument = $node->args[0]->value;
        if ($argument instanceof LNumber) {
            if ($argument->value === 0) {
                // Replace `ob_implicit_flush(0)` with `ob_implicit_flush(false)`
                $node->args[0]->value = new ConstFetch(new Name('false'));
            } elseif ($argument->value === 1) {
                // Replace `ob_implicit_flush(1)` with `ob_implicit_flush(true)`
                $node->args[0]->value = new ConstFetch(new Name('true'));
            }
        }

        return $node;
    }
}
