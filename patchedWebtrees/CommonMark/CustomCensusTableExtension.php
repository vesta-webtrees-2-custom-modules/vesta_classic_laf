<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\CommonMark;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableCellRenderer;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableRowRenderer;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Extension\Table\TableSectionRenderer;

/**
 * Convert webtrees 1.x census-assistant markup into tables.
 * Note that webtrees 2.0 generates markdown tables directly.
 *
 * Based on the table parser from league/commonmark-ext-table.
 */
class CustomCensusTableExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment
            ->addBlockParser(new CustomCensusTableParser())
            ->addBlockRenderer(Table::class, new TableRenderer())
            ->addBlockRenderer(TableSection::class, new TableSectionRenderer())
            ->addBlockRenderer(TableRow::class, new TableRowRenderer())
            ->addBlockRenderer(TableCell::class, new TableCellRenderer());
    }
}
