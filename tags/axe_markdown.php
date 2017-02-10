<?php
declare(strict_types=1);
/**
 * Minotaur
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @copyright 2015-2017 Appertly
 * @license   Apache-2.0
 */

use Minotaur\Tags\Composited;
use Minotaur\Tags\Frag;
use Minotaur\Tags\Node as TNode;
use Minotaur\Tags\Raw;
use Minotaur\Tags\Tag;
use function Minotaur\Tags\fcomposited;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\BlockQuote;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Element\HtmlBlock;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Block\Element\ListBlock;
use League\CommonMark\Block\Element\ListItem;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Element\ThematicBreak;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\HtmlInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Newline;
use League\CommonMark\Inline\Element\Strong;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;

/**
 * Turns Markdown syntax into XHP nodes.
 */
class axe_markdown extends Composited
{
    private const EMPTY_ARRAY = [];

    protected function render(): TNode
    {
        $text = $this->getAttribute('text');
        if ($text === null) {
            return new Frag();
        }
        $text = (string)$text;
        if ($text === '') {
            return new Frag();
        }
        $parser = $this->ensureAttribute('docParser', DocParser::class);
        if ($parser === null) {
            $environment = Environment::createCommonMarkEnvironment();
            $parser = new DocParser($environment);
        }
        $document = $parser->parse($text);
        $blocks = $this->renderBlocks($document->children());
        return $blocks === null ? new Frag() : new Frag($blocks);
    }

    /**
     * @var iterable<AbstractInline>
     * @return array<Child>
     */
    private function renderInlines(iterable $inlines)
    {
        $results = [];
        foreach ($inlines as $inline) {
            $results[] = $this->renderInline($inline);
        }
        return $results;
    }

    private function renderInline(AbstractInline $inline)
    {
        if ($inline instanceof Code) {
            $b = new Tag('code', [], $inline->getContent());
            $b->setAttributes($inline->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        } elseif ($inline instanceof Emphasis) {
            $b = new Tag('em', [], $this->renderInlines($inline->children()));
            $b->setAttributes($inline->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        } elseif ($inline instanceof HtmlInline) {
            return $this->factoryUnsafe($inline->getContent());
        } elseif ($inline instanceof Image) {
            $b = new Tag('img');
            $b->setAttributes($inline->getData('attributes', []) ?? self::EMPTY_ARRAY);
            $b->setAttribute('src', $inline->getUrl());
            $alt = implode("", $this->renderInlines($inline->children()));
            $alt = preg_replace('/\<[^>]*alt="([^"]*)"[^>]*\>/', '$1', $alt);
            $b->setAttribute('alt', preg_replace('/\<[^>]*\>/', '', $alt));
            $t = (string)($inline->data['title'] ?? '');
            if (strlen(trim($t)) > 0) {
                $b->setAttribute('title', $inline->data['title']);
            }
            return $b;
        } elseif ($inline instanceof Link) {
            $b = new Tag('a', [], $this->renderInlines($inline->children()));
            $b->setAttributes($inline->getData('attributes', []) ?? self::EMPTY_ARRAY);
            $b->setAttribute('href', $inline->getUrl());
            $t = (string)($inline->data['title'] ?? '');
            if (strlen(trim($t)) > 0) {
                $b->setAttribute('title', $inline->data['title']);
            }
            return $b;
        } elseif ($inline instanceof Newline) {
            if ($inline->getType() === Newline::HARDBREAK) {
                return new Tag('br');
            } else {
                return new Frag("\n");
            }
        } elseif ($inline instanceof Strong) {
            $b = new Tag('strong', [], $this->renderInlines($inline->children()));
            $b->setAttributes($inline->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        } elseif ($inline instanceof Text) {
            return new Frag($inline->getContent());
        }

        return new Frag();
    }

    /**
     * @param iterable<AbstractBlock> $blocks
     * @return array<Child>
     */
    private function renderBlocks(iterable $blocks, bool $inTightList = false)
    {
        $results = [];
        foreach ($blocks as $block) {
            $results[] = $this->renderBlock($block, $inTightList);
        }
        return $results;
    }

    private function renderBlock(AbstractBlock $block, bool $inTightList = false)
    {
        if ($block instanceof BlockQuote) {
            $filling = $this->renderBlocks($block->children());
            $b = new Tag('blockquote', [], $filling);
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        } elseif ($block instanceof FencedCode) {
            $infoWords = $block->getInfoWords();
            $b = new Tag('code', [], $block->getStringContent());
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            if (count($infoWords) !== 0 && strlen($infoWords[0]) !== 0) {
                $b->addClass('language-' . $infoWords[0]);
            }
            return new Tag('pre', [], $b);
        } elseif ($block instanceof Heading) {
            $kids = $this->renderInlines($block->children());
            $b = new Tag('h' . $block->getLevel(), [], $kids);
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        } elseif ($block instanceof HtmlBlock) {
            return $this->factoryUnsafe($block->getStringContent());
        } elseif ($block instanceof IndentedCode) {
            $b = new Tag('code', [], $block->getStringContent());
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return new Tag('pre', [], $b);
        } elseif ($block instanceof ListBlock) {
            $listData = $block->getListData();
            $b = new Tag($listData->type === ListBlock::TYPE_UNORDERED ? 'ul' : 'ol');
            $b->appendChild(
                $this->renderBlocks($block->children(), $block->isTight())
            );
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            if ($listData->start !== null && $listData->start !== 1) {
                $b->setAttribute('start', (int) $listData->start);
            }
            return $b;
        } elseif ($block instanceof ListItem) {
            $contents = $this->renderBlocks($block->children(), $inTightList);
            $b = new Tag('li', [], $contents);
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        } elseif ($block instanceof Paragraph) {
            if ($inTightList) {
                return new Frag($this->renderInlines($block->children()));
            } else {
                $b = new Tag('p', [], $this->renderInlines($block->children()));
                $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
                return $b;
            }
        } elseif ($block instanceof ThematicBreak) {
            $b = new Tag('hr');
            $b->setAttributes($block->getData('attributes', []) ?? self::EMPTY_ARRAY);
            return $b;
        }

        return new Frag();
    }

    private function factoryUnsafe(string $content)
    {
        return new class($content) implements Raw {
            private $content;

            public function __construct($content)
            {
                $this->content = $content;
            }

            public function jsonSerialize()
            {
                return $this->content;
            }

            public function __toString(): string
            {
                return $this->content;
            }

            public function toHtmlString(): string
            {
                return $this->content;
            }
        };
    }
}
