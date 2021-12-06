<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\CommonMark;

use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Parser\BlockParserInterface;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;

use function array_shift;
use function explode;
use function str_starts_with;
use function substr;

/**
 * Convert webtrees 1.x census-assistant markup into tables.
 * Note that webtrees 2.0 generates markdown tables directly.
 *
 * Based on the table parser from webuni/commonmark-table-extension.
 */
class CustomCensusTableParser implements BlockParserInterface
{
    // Keywords used to create the webtrees 1.x census-assistant notes.
    private const CA_PREFIX = '.start_formatted_area.';
    private const CA_SUFFIX = '.end_formatted_area.';
    private const TH_PREFIX = '.b.';

    //[PATCHED]
    private const CA_PREFIX2 = '.start_formatted_area.  ';
    private const CA_SUFFIX2 = '.end_formatted_area.  ';

    /**
     * Parse a paragraph of text with the following structure:
     *
     * .start_formatted_area.
     * .b.HEADING1|.b.HEADING2|.b.HEADING3
     * COL1|COL2|COL3
     * COL1|COL2|COL3
     * .end_formatted_area.
     *
     * @param ContextInterface $context
     * @param Cursor           $cursor
     *
     * @return bool
     */
    public function parse(ContextInterface $context, Cursor $cursor): bool
    {
        $container = $context->getContainer();

        if (!$container instanceof Paragraph) {
            return false;
        }

        $lines = $container->getStrings();
        $first = array_shift($lines);
    
        //[PATCHED]
        if (($first !== self::CA_PREFIX) && ($first !== self::CA_PREFIX2)) {
            return false;
        }

        //[PATCHED]
        if (($cursor->getLine() !== self::CA_SUFFIX) && ($cursor->getLine() !== self::CA_SUFFIX2)) {
            return false;
        }

        // We don't need to parse/markup any of the table's contents.
        $table = new Table(static function (): bool {
            return false;
        });

        // First line is the table header.
        $line = array_shift($lines);
        $row  = $this->parseRow($line, TableCell::TYPE_HEAD);
        $table->getHead()->appendChild($row);

        // Subsequent lines are the table body.
        while ($lines !== []) {
            $line = array_shift($lines);
            $row  = $this->parseRow($line, TableCell::TYPE_BODY);
            $table->getBody()->appendChild($row);
        }

        $context->replaceContainerBlock($table);

        return true;
    }

    /**
     * @param string $line
     * @param string $type
     *
     * @return TableRow
     */
    private function parseRow(string $line, string $type): TableRow
    {
        $cells = explode('|', $line);
        $row   = new TableRow();

        foreach ($cells as $cell) {
            if (str_starts_with($cell, self::TH_PREFIX)) {
                $cell = substr($cell, strlen(self::TH_PREFIX));
                $type = TableCell::TYPE_HEAD;
            }

            $row->appendChild(new TableCell($cell, $type, null));
        }

        return $row;
    }
}
