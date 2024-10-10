<?php

declare(strict_types=1);

namespace OrangeHRM\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConvertToMbstringRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace standard string functions with mbstring equivalents',
            [
                new CodeSample(
                    'strlen($string);',
                    'mb_strlen($string, "UTF-8");'
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof FuncCall) {
            return null;
        }

        // Map of standard functions and their mbstring equivalents
        $functionMapping = [
            'strlen' => 'mb_strlen',
            'substr' => 'mb_substr',
            'strpos' => 'mb_strpos',
            'strrpos' => 'mb_strrpos',
            'strtolower' => 'mb_strtolower',
            'strtoupper' => 'mb_strtoupper',
            'substr_count' => 'mb_substr_count',
        ];

        // Check if the function is one of the standard functions we want to replace
        $functionName = $this->getName($node);
        if (!isset($functionMapping[$functionName])) {
            return null;
        }

        // Replace with the mbstring equivalent
        $node->name = new Name($functionMapping[$functionName]);

        // Add the encoding parameter for mbstring functions if it's not already set
        if (count($node->args) < 2) {
            $node->args[] = $this->nodeFactory->createArg('UTF-8');
        }

        return $node;
    }
}