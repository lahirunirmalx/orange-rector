<?php

declare(strict_types=1);

namespace OrangeHRM\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;

final class ReplaceLibxmlDisableEntityLoaderRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace `libxml_disable_entity_loader()` with an if statement checking `PHP_VERSION_ID < 80000`',
            [
                new CodeSample(
                // Original code
                    <<<'CODE_SAMPLE'
libxml_disable_entity_loader(true);
CODE_SAMPLE
                    ,
                    // Transformed code
                    <<<'CODE_SAMPLE'
if (PHP_VERSION_ID < 80000) {
    libxml_disable_entity_loader(true);
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * This rule is applied to function call nodes (`FuncCall`).
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * The main refactoring logic to transform `libxml_disable_entity_loader` calls.
     */
    public function refactor(Node $node): ?Node
    {
        // Check if the node is a call to `libxml_disable_entity_loader()`
        if (!$this->isName($node, 'libxml_disable_entity_loader')) {
            return null;
        }

        // Check if it has the correct argument `true`
        if (!$this->isLibxmlDisableEntityLoaderTrue($node)) {
            return null;
        }

        // Create the condition `PHP_VERSION_ID < 80000`
        $phpVersionCheck = new Node\Expr\BinaryOp\Smaller(
            new ConstFetch(new Node\Name('PHP_VERSION_ID')),
            new LNumber(80000)
        );

        // Create the if statement wrapping the `libxml_disable_entity_loader(true)` function call
        $newIfNode = new If_(
            $phpVersionCheck,
            [
                'stmts' => [
                    new Expression($node) // Wrap the original `libxml_disable_entity_loader(true);` call in the if statement
                ],
            ]
        );

        return $newIfNode;
    }

    /**
     * Check if the function call is `libxml_disable_entity_loader(true)`.
     */
    private function isLibxmlDisableEntityLoaderTrue(FuncCall $funcCall): bool
    {
        // Ensure the function has exactly one argument and it is `true`
        return isset($funcCall->args[0]) && $this->valueResolver->isTrue($funcCall->args[0]->value);
    }
}
