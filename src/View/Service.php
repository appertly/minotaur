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

use Caridea\Container\EmptyContainer;

/**
 * Creates Views and broadcasts the render event.
 */
class Service implements \Caridea\Container\ContainerAware
{
    use \Caridea\Container\ContainerSetter;

    /**
     * @var \Minotaur\View\Page The page, or null
     */
    private $page;

    /**
     * @var \Minotaur\View\BlockLayout The block layout, or null
     */
    private $blocks;

    /**
     * @var array<string> List of statuses
     */
    private const STATUSES = ['msg-warning', 'msg-info', 'msg-error'];

    /**
     * Creates a new ViewService.
     *
     * @param $container - The dependency injection container
     */
    public function __construct(?\Caridea\Container\Container $container)
    {
        $this->container = $container ?? new EmptyContainer();
    }

    /**
     * Gets the Page for this request (created lazily).
     *
     * @param string $title The page title
     * @return \Minotaur\View\Page A Page
     */
    public function getPage(string $title): Page
    {
        if ($this->page === null) {
            $page = (new Page())->setTitle($this->getPageTitle($title));
            $this->callPageVisitors($page);
            $this->page = $page;
        }
        return $this->page;
    }

    /**
     * Calls the page visitors.
     *
     * @param \Minotaur\View\Page $page The page to visit
     */
    protected function callPageVisitors(Page $page)
    {
        return array_map(function (PageVisitor $v) use ($page) {
            $v->visit($page);
        }, $this->container->getByType(PageVisitor::class));
    }

    /**
     * Generates a page title.
     *
     * @param string|null $title The page title
     * @return string The formatted page title
     */
    protected function getPageTitle(?string $title): string
    {
        return sprintf(
            $this->container->get('web.ui.title.template'),
            $title,
            $this->container->get('system.name')
        );
    }

    /**
     * Sets a Flash Message.
     *
     * @param string $name The status
     * @param string $value The message
     * @param bool $current Whether to add message to the current request
     */
    public function setFlashMessage(string $name, string $value, bool $current = false): void
    {
        $session = $this->container->getFirst(\Caridea\Session\Session::class);
        if ($session === null) {
            throw new \UnexpectedValueException("No Session Manager found");
        }
        $session->resume() || $session->start();
        $flash = $this->container->getFirst(\Caridea\Session\FlashPlugin::class);
        if ($flash === null) {
            throw new \UnexpectedValueException("No Flash Plugin found");
        }
        $flash->set($name, $value, $current);
    }

    /**
     * Clears Flash Messages.
     *
     * @param bool $current Whether to add message to the current request
     */
    public function clearFlashMessages(bool $current = false): void
    {
        $session = $this->container->getFirst(\Caridea\Session\Session::class);
        if ($session === null) {
            throw new \UnexpectedValueException("No Session Manager found");
        }
        $session->resume() || $session->start();
        $flash = $this->container->getFirst(\Caridea\Session\FlashPlugin::class);
        if ($flash === null) {
            throw new \UnexpectedValueException("No Flash Plugin found");
        }
        $flash->clear($current);
    }

    /**
     * Keeps all current flash messages for the next request.
     */
    public function keepFlashMessages(): void
    {
        $session = $this->container->getFirst(\Caridea\Session\Session::class);
        if ($session === null) {
            throw new \UnexpectedValueException("No Session Manager found");
        }
        $session->resume() || $session->start();
        $flash = $this->container->getFirst(\Caridea\Session\FlashPlugin::class);
        if ($flash === null) {
            throw new \UnexpectedValueException("No Flash Plugin found");
        }
        $flash->keep();
    }

    /**
     * Gets any flash messages in the session keyed by status.
     *
     * @return array<string,array<string>> map of flash messages
     */
    public function getFlashMessages(): array
    {
        $plugin = $this->container->getFirst(\Caridea\Session\FlashPlugin::class);
        if ($plugin === null) {
            throw new \UnexpectedValueException("No Flash Plugin found");
        }
        $map = [];
        foreach (self::STATUSES as $status) {
            $messages = $plugin->getCurrent($status);
            if (!$messages) {
                continue;
            }
            $vector = [];
            if (is_iterable($messages)) {
                foreach ($messages as $message) {
                    $vector[] = (string) $message;
                }
            } else {
                $vector[] = (string) $messages;
            }
            $map[$status] = $vector;
        }
        return $map;
    }

    /**
     * Gets the last request that the Dispatcher sent to a controller.
     *
     * @return \Psr\Http\Message\ServerRequestInterface The last dispatched request, or `null`
     */
    public function getDispatchedRequest(): ?\Psr\Http\Message\ServerRequestInterface
    {
        $c = $this->container ?? new EmptyContainer();
        $d = current($c->getByType(\Minotaur\Route\Dispatcher::class));
        return $d === null ? null : $d->getLastDispatchedRequest();
    }

    /**
     * Gets all blocks registered for a given region
     *
     * @param string $region The region to search
     * @return array<\Minotaur\View\Block> The found blocks in that region, or an empty array.
     */
    public function getBlocks(string $region): array
    {
        $c = $this->container ?? new EmptyContainer();
        $blocks = [];
        foreach ($this->getBlockLayout()->get($region) as $name) {
            $blocks[] = $c->named($name, Block::class);
        }
        return $blocks;
    }

    protected function getBlockLayout(): BlockLayout
    {
        if ($this->blocks === null) {
            $layout = new BlockLayout();
            $c = $this->container ?? new EmptyContainer();
            $layouts = $c->getByType(BlockLayout::class);
            if (count($layouts) === 0) {
                foreach ($c->getByType(Block::class) as $name => $block) {
                    if (method_exists($block, 'getRegion')) {
                        $region = $block->getRegion();
                        $order = !method_exists($block, 'getOrder') ? 0 :
                            (int) $block->getOrder();
                        $layout->add($region, $order, $name);
                    }
                }
            } else {
                foreach ($layouts as $bl) {
                    $layout->merge($bl);
                }
            }
            $this->blocks = $layout;
        }
        return $this->blocks;
    }

    /**
     * Gets any `Minotaur\Db\DbRefResolver` objects in the container.
     *
     * @return array<\Minotaur\Db\DbRefResolver> The DbRefResolver objects found.
     */
    public function getDbRefResolvers(): array
    {
        return $this->container->getByType(\Minotaur\Db\DbRefResolver::class);
    }

    /**
     * Gets any `Minotaur\View\EntityLinker` objects in the container.
     *
     * @return array<\Minotaur\View\EntityLinker> The EntityLinker objects found.
     */
    public function getEntityLinkers(): array
    {
        return $this->container->getByType(EntityLinker::class);
    }

    /**
     * Gets the CSRF token.
     *
     * @return string|null The CSRF token or `null`
     * @throws \UnexpectedValueException if the plugin wasn't in the container
     */
    public function getCsrfToken(): ?string
    {
        $plugin = $this->container->getFirst(\Caridea\Session\CsrfPlugin::class);
        if ($plugin === null) {
            throw new \UnexpectedValueException("No CSRF Plugin found");
        }
        return $plugin->getValue();
    }

    /**
     * Checks to see if the provided token matches the session CSRF token.
     *
     * @param string the provided token
     * @return bool whether the provided token matches
     * @throws \UnexpectedValueException if the plugin wasn't in the container
     */
    public function isCsrfValid(string $token): bool
    {
        $plugin = $this->container->getFirst(\Caridea\Session\CsrfPlugin::class);
        if ($plugin === null) {
            throw new \UnexpectedValueException("No CSRF Plugin found");
        }
        return $plugin->isValid($token);
    }
}
