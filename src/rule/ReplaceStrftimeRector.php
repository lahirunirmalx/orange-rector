<?php

declare(strict_types=1);

namespace OrangeHRM\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ReplaceStrftimeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace deprecated `strftime()` calls with `date()` or `IntlDateFormatter::format()`',
            [
                new CodeSample(
                // Original deprecated function
                    <<<'CODE_SAMPLE'
echo strftime('%Y-%m-%d', time());
CODE_SAMPLE
                    ,
                    // Replaced with `date()` function
                    <<<'CODE_SAMPLE'
echo date('Y-m-d', time());
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * Define the types of nodes this Rector rule will be interested in.
     * In this case, we want to look at `FuncCall` nodes.
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * Process the nodes that match the `getNodeTypes()` criteria.
     * This method is called for each `FuncCall` node.
     */
    public function refactor(Node $node): ?Node
    {
        // Check if the function call is `strftime`
        if (!$this->isName($node, 'strftime')) {
            return null;
        }

        // Check if `strftime` has at least one argument
        if (count($node->args) === 0) {
            return null;
        }

        // Extract the format string (first argument)
        $formatArgument = $node->args[0]->value;

        // Convert strftime format to date() format
        $dateFormat = $this->convertStrftimeToDateFormat($formatArgument);

        // Create a new `date()` function call with the converted format and optional timestamp
        $dateFunctionCall = new FuncCall(
            new Node\Name('date'),
            [
                new String_($dateFormat), // Converted date format
                isset($node->args[1]) ? $node->args[1] : new FuncCall(new Node\Name('time')) // Use the second argument as timestamp or default to `time()`
            ]
        );

        return $dateFunctionCall;
    }

    /**
     * Converts a `strftime` format to an equivalent `date` format.
     * This is a simplified example and may need to be expanded for complex formats.
     */
    private function convertStrftimeToDateFormat(Node $formatArgument): string
    {
        if (!$formatArgument instanceof String_) {
            return '';
        }

        // Map of strftime format specifiers to date() format specifiers
        $conversionMap = [
            '%Y' => 'Y',  // 4-digit year
            '%y' => 'y',  // 2-digit year
            '%m' => 'm',  // Month number (01–12)
            '%d' => 'd',  // Day of the month (01–31)
            '%H' => 'H',  // 24-hour format of an hour (00–23)
            '%M' => 'i',  // Minute (00–59)
            '%S' => 's',  // Second (00–59)
            '%A' => 'l',  // Full weekday name (Sunday–Saturday)
            '%B' => 'F',  // Full month name (January–December)
            '%j' => 'z',  // Day of the year (001–366)
            '%U' => 'W',  // ISO-8601 week number of year (weeks starting on Monday)
        ];

        // Convert the strftime format to date format using the conversion map
        $strftimeFormat = $formatArgument->value;
        $dateFormat = strtr($strftimeFormat, $conversionMap);

        return $dateFormat;
    }
}
