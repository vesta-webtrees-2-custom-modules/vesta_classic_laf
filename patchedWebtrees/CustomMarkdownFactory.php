<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Cissee\WebtreesExt\CommonMark\CustomCensusTableExtension;
use Fisharebest\Webtrees\CommonMark\ResponsiveTableExtension;
use Fisharebest\Webtrees\CommonMark\XrefExtension;
use Fisharebest\Webtrees\Contracts\MarkdownFactoryInterface;
use Fisharebest\Webtrees\Tree;
use League\CommonMark\Block\Element\Document;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Renderer\DocumentRenderer;
use League\CommonMark\Block\Renderer\ParagraphRenderer;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Renderer\LinkRenderer;
use League\CommonMark\Inline\Renderer\TextRenderer;

/**
 * Create a markdown converter.
 */
class CustomMarkdownFactory implements MarkdownFactoryInterface
{
    protected const CONFIG = [
        'allow_unsafe_links' => false,
        'html_input'         => EnvironmentInterface::HTML_INPUT_ESCAPE,
    ];

    /**
     * @param Tree|null $tree
     *
     * @return CommonMarkConverter
     */
    public function autolink(string $markdown, Tree $tree = null): string
    {
        // Create a minimal commonmark processor - just add support for auto-links.
        $environment = new Environment();
        $environment->addBlockRenderer(Document::class, new DocumentRenderer());
        $environment->addBlockRenderer(Paragraph::class, new ParagraphRenderer());
        $environment->addInlineRenderer(Text::class, new TextRenderer());
        $environment->addInlineRenderer(Link::class, new LinkRenderer());
        $environment->addExtension(new AutolinkExtension());

        // Optionally create links to other records.
        if ($tree instanceof Tree) {
            $environment->addExtension(new XrefExtension($tree));
        }

        $converter = new MarkDownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }

    /**
     * @param Tree|null $tree
     *
     * @return CommonMarkConverter
     */
    public function markdown(string $markdown, Tree $tree = null): string
    {
        $environment = Environment::createCommonMarkEnvironment();

        // Wrap tables so support horizontal scrolling with bootstrap.
        $environment->addExtension(new ResponsiveTableExtension());

        // Convert webtrees 1.x style census tables to commonmark format.
        //[PATCHED]
        $environment->addExtension(new CustomCensusTableExtension());

        // Optionally create links to other records.
        if ($tree instanceof Tree) {
            $environment->addExtension(new XrefExtension($tree));
        }

        $converter = new MarkDownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }
}
