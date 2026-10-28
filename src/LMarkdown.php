<?php

declare(strict_types=1);

namespace Lpdf;

use Lpdf\Markdown\MarkdownAAttr;
use Lpdf\Markdown\MarkdownCodeAttr;
use Lpdf\Markdown\MarkdownContentNode;
use Lpdf\Markdown\MarkdownDelAttr;
use Lpdf\Markdown\MarkdownEmAttr;
use Lpdf\Markdown\MarkdownH1Attr;
use Lpdf\Markdown\MarkdownH2Attr;
use Lpdf\Markdown\MarkdownH3Attr;
use Lpdf\Markdown\MarkdownH4Attr;
use Lpdf\Markdown\MarkdownH5Attr;
use Lpdf\Markdown\MarkdownH6Attr;
use Lpdf\Markdown\MarkdownLiAttr;
use Lpdf\Markdown\MarkdownPAttr;
use Lpdf\Markdown\MarkdownQuoteAttr;
use Lpdf\Markdown\MarkdownRuleNode;
use Lpdf\Markdown\MarkdownStrongAttr;
use Lpdf\Markdown\MarkdownStylesAttr;
use Lpdf\Markdown\MarkdownStylesNode;
use Lpdf\Markdown\MarkdownTableAttr;
use Lpdf\Markdown\MarkdownTdAttr;
use Lpdf\Markdown\MarkdownThAttr;
use Lpdf\Shared\AttrsHelper;

/**
 * The parts of a markdown: its styles, its content and the rules of its styles. The markdown itself is
 * {@see L::markdown()}.
 *
 * @example
 * ```php
 * L::markdown(new MarkdownAttr(margin: '54pt', gap: 'm'), [
 *     LMarkdown::styles(NoAttr, [LMarkdown::h1(new MarkdownH1Attr(color: 'muted'))]),
 *     LMarkdown::content(NoAttr, [$body]),
 * ])
 * ```
 */
final class LMarkdown
{
    use AttrsHelper;

    /**
     * Build the rules that say how the parts of a markdown look. A rule is named after the Markdown it styles
     * and takes the attributes of the element it becomes. A rule here overrides the same rule of the file that
     * `src` names, which overrides the defaults.
     *
     * @param MarkdownRuleNode[] $nodes
     */
    public static function styles(?MarkdownStylesAttr $attrs = null, array $nodes = []): MarkdownStylesNode
    {
        return new MarkdownStylesNode(self::optionsToAttrs($attrs), $nodes);
    }

    /**
     * Build the Markdown, as strings that make one text. The common indent of its lines is removed, so that
     * indenting the code does not make a code block of it. It has no attributes: pass `NoAttr`.
     *
     * @param string[] $nodes
     */
    public static function content(null $attrs = null, array $nodes = []): MarkdownContentNode
    {
        foreach ($nodes as $i => $child) {
            if (!is_string($child)) {
                throw new \InvalidArgumentException(
                    "content() child at index $i must be a string, got " . get_debug_type($child),
                );
            }
        }
        return new MarkdownContentNode($nodes);
    }

    /** Build the rule of a heading of level 1. */
    public static function h1(?MarkdownH1Attr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('h1', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a heading of level 2. */
    public static function h2(?MarkdownH2Attr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('h2', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a heading of level 3. */
    public static function h3(?MarkdownH3Attr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('h3', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a heading of level 4. */
    public static function h4(?MarkdownH4Attr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('h4', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a heading of level 5. */
    public static function h5(?MarkdownH5Attr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('h5', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a heading of level 6. */
    public static function h6(?MarkdownH6Attr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('h6', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a paragraph, and of the words of a list item, a quote and a footnote. */
    public static function p(?MarkdownPAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('p', self::optionsToAttrs($attrs));
    }

    /** Build the rule of code: the box of a block, and the run of inline code. */
    public static function code(?MarkdownCodeAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('code', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a block quote. */
    public static function quote(?MarkdownQuoteAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('quote', self::optionsToAttrs($attrs));
    }

    /** Build the rule of an item of a bullet or a numbered list. */
    public static function li(?MarkdownLiAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('li', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a link. */
    public static function a(?MarkdownAAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('a', self::optionsToAttrs($attrs));
    }

    /** Build the rule of strong text. */
    public static function strong(?MarkdownStrongAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('strong', self::optionsToAttrs($attrs));
    }

    /** Build the rule of emphasis. */
    public static function em(?MarkdownEmAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('em', self::optionsToAttrs($attrs));
    }

    /** Build the rule of struck-through text. */
    public static function del(?MarkdownDelAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('del', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a table. */
    public static function table(?MarkdownTableAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('table', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a cell of a table's header row. */
    public static function th(?MarkdownThAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('th', self::optionsToAttrs($attrs));
    }

    /** Build the rule of a cell of a table's other rows. */
    public static function td(?MarkdownTdAttr $attrs = null): MarkdownRuleNode
    {
        return new MarkdownRuleNode('td', self::optionsToAttrs($attrs));
    }
}
