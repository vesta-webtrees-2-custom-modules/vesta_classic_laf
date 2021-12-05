<?php

declare(strict_types=1);

namespace Fisharebest\Webtrees;

use Fisharebest\Webtrees\Registry;

/**
 * Filter input and escape output.
 *
 * @deprecated since 2.0.17. Will be removed in 2.1.0.
 */
class Filter
{
    /**
     * Format block-level text such as notes or transcripts, etc.
     *
     * @param string $text
     * @param Tree   $tree
     *
     * @return string
     */
    public static function formatText(string $text, Tree $tree): string
    {
        switch ($tree->getPreference('FORMAT_TEXT')) {
            case 'markdown':
                //[PATCHED]
                $text = str_replace("\n","  \n", $text);
              
                return self::markdown($text, $tree);
            default:
                return self::expandUrls($text, $tree);
        }
    }

    /**
     * Format a block of text, expanding URLs and XREFs.
     *
     * @param string $text
     * @param Tree   $tree
     *
     * @return string
     */
    public static function expandUrls(string $text, Tree $tree): string
    {
        return Registry::markdownFactory()->autolink($tree)->convertToHtml($text);
    }

    /**
     * Format a block of text, using "Markdown".
     *
     * @param string $text
     * @param Tree   $tree
     *
     * @return string
     */
    public static function markdown(string $text, Tree $tree): string
    {
        return Registry::markdownFactory()->markdown($tree)->convertToHtml($text);
    }
}
