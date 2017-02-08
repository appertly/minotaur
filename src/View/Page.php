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
namespace Minotaur\View;

/**
 * Stores page information to pass along to rendering functions.
 */
class Page
{
    /**
     * @var string
     */
    private $title = '';
    /**
     * @var string
     */
    private $id = '';
    /**
     * @var string
     */
    private $lang = 'en';
    /**
     * @var string
     */
    private $encoding = 'utf8';
    /**
     * @var array<string>
     */
    private $classes = [];
    /**
     * @var array<mixed>
     */
    // TODO figure out type
    private $metas = [];
    /**
     * @var array<mixed>
     */
    // TODO figure out type
    private $links = [];
    /**
     * @var array<mixed>
     */
    // TODO figure out type
    private $headScripts = [];
    // TODO figure out type
    private $bodyScripts = [];

    /**
     * Gets the classes for the <body> tag.
     *
     * @return array<string>
     */
    public function getBodyClasses(): array
    {
        return $this->classes;
    }

    /**
     * Adds CSS classes to those for the <body> tag.
     *
     * @param iterable<string> The CSS classes
     */
    public function addBodyClasses(iterable $classes): self
    {
        $this->classes->addAll($classes);
        return $this;
    }

    /**
     * Sets the page language.
     *
     * @param $lang - The new language
     * @return self provides a fluent interface
     */
    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Gets the page language, by default en.
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * Gets the page encoding, by default utf8.
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Sets the page encoding (e.g. utf8)
     *
     * @param $encoding - The page encoding
     * @return self provides a fluent interface
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Gets the page title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Gets the page <meta/> tags.
     */
    public function getMeta(): array
    {
        return $this->metas;
    }

    /**
     * Gets the page <link/> tags.
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Gets the <script> tags in the page head.
     */
    // TODO figure out return type
    public function getHeadScripts(): array
    {
        return $this->headScripts;
    }

    /**
     * Gets the <script> tags in the page body.
     *
     * @return array<mixed>
     */
    // TODO figure out return type
    public function getBodyScripts(): array
    {
        return $this->bodyScripts;
    }

    /**
     * Sets the page title.
     *
     * @param $title - The new page title
     * @return self provides a fluent interface
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gets the page id.
     */
    public function getBodyId(): string
    {
        return $this->id;
    }

    /**
     * Sets the page id.
     *
     * @param $id - The new id
     * @return self provides a fluent interface
     */
    public function setBodyId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Adds a <meta> tag.
     *
     * @param $name - The tag name attribute
     * @param $content - The tag content attribute
     * @return self provides a fluent interface
     */
    public function addMeta(string $name, string $content): self
    {
        // TODO figure out XHP replacement
        // $this->metas->add(<meta name={$name} content={$content} />);
        return $this;
    }

    /**
     * Adds a stylesheet.
     *
     * @param $src - The file location
     * @param $mime - Optional MIME type
     * @param iterable<string> $media Optional list of media types
     * @return self provides a fluent interface
     */
    public function addStylesheet(string $src, string $mime = '', iterable $media = null): self
    {
        // TODO figure out XHP replacement
        // $link = <link rel="stylesheet" href={$src} />;
        // if (strlen($mime) > 0) {
        //     $link->setAttribute('type', $mime);
        // }
        // if ($media !== null) {
        //     $link->setAttribute('media', implode(',', $media));
        // }
        // $this->links[] = $link;
        return $this;
    }

    /**
     * Adds a <link> tag.
     *
     * @param $rel - The relationship
     * @param $href - The resource HREF
     * @param $sizes - Optional sizes
     * @param $crossorigin - Optional crossorigin
     * @param $integrity - Optional integrity
     * @return self provides a fluent interface
     */
    public function addLink(string $rel, string $href, string $sizes = '', string $crossorigin = '', string $integrity = ''): self
    {
        // TODO figure out XHP replacement
        // $link = <link rel={$rel} href={$href} />;
        // if (strlen($sizes) > 0) {
        //     $link->setAttribute('sizes', $sizes);
        // }
        // if (strlen($crossorigin) > 0) {
        //     $link->setAttribute('crossorigin', $crossorigin);
        // }
        // if (strlen($integrity) > 0) {
        //     $link->setAttribute('integrity', $integrity);
        // }
        // $this->links[] = $link;
        return $this;
    }

    /**
     * Adds an external script to the head.
     *
     * @param $src - The script location
     * @param $mime - Optional MIME type
     * @return self provides a fluent interface
     */
    public function addHeadScript(string $src, string $mime = ''): self
    {
        // TODO figure out XHP replacement
        // $script = <script src={$src}></script>;
        // if (strlen($mime) > 0) {
        //     $script->setAttribute('type', $mime);
        // }
        // $this->headScripts[] = $script;
        return $this;
    }

    /**
     * Adds an inline script to the head.
     *
     * @param $script - The inline JavaScript
     * @param $mime - Optional MIME type
     * @return self provides a fluent interface
     */
    public function addHeadScriptInline(string $script, string $mime = ''): self
    {
        // TODO figure out XHP replacement
        // $script = <script>{$script}</script>;
        // if (strlen($mime) > 0) {
        //     $script->setAttribute('type', $mime);
        // }
        // $this->headScripts[] = $script;
        return $this;
    }

    /**
     * Adds an external script to the body.
     *
     * @param $src - The script location
     * @param $mime - Optional MIME type
     * @return self provides a fluent interface
     */
    public function addBodyScript(string $src, string $mime = ''): self
    {
        // TODO figure out XHP replacement
        // $script = <script src={$src}></script>;
        // if (strlen($mime) > 0) {
        //     $script->setAttribute('type', $mime);
        // }
        // $this->bodyScripts[] = $script;
        return $this;
    }

    /**
     * Adds an inline script to the mody.
     *
     * @param $script - The inline JavaScript
     * @param $mime - Optional MIME type
     * @return self provides a fluent interface
     */
    public function addBodyScriptInline(string $script, string $mime = ''): self
    {
        // TODO figure out XHP replacement
        // $script = <script>{$script}</script>;
        // if (strlen($mime) > 0) {
        //     $script->setAttribute('type', $mime);
        // }
        // $this->bodyScripts[] = $script;
        return $this;
    }
}
