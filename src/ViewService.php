<?php

/**
 * Responsible for setting the view configurations.
 *
 * @package WPStrap\Libs
 */

declare(strict_types=1);

namespace WPStrap\View;

/**
 * Class ViewService
 */
class ViewService extends AbstractView implements ViewInterface
{
    /**
     * The project dir path.
     *
     * @var string
     */
    protected string $dir;

    /**
     * The project hook prefix.
     *
     * @var string
     */
    protected string $hook;

    /**
     * The view folders name.
     *
     * @var string
     */
    protected string $folder;

    /**
     * The path to views folder.
     *
     * @var string
     */
    protected string $path;

    /**
     * Entry to find view folder in domain
     *
     * @var string
     */
    protected string $entry;

    /**
     * Whether to locate views
     *
     * @var string
     */
    protected string $locate;

    /**
     * @inheritDoc
     */
    public function register(array $config): self
    {
        foreach (['dir', 'hook', 'folder', 'path', 'entry', 'locate'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = \untrailingslashit($config[$key]);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(string ...$paths): self
    {
        if (empty($paths[1])) {
            $this->view = ['slug' => $paths[0]];
        } else {
            $this->view = [
                'domain' => $paths[0],
                'slug' => $paths[1]
            ];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function args(array $args): self
    {
        if (!isset($this->view['slug'])) {
            \wp_die('[View] args() shouldn\'t be called before render().');
        }

        $this->view['args'] = \apply_filters("{$this->getHook()}_view_args", $args, $this->view['slug'], $this->view['domain'] ?? null);

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getViewFile(): string
    {
        return $this->view['slug'] . '.php';
    }

    /**
     * @inheritDoc
     */
    protected function getAbsoluteViewPath(): string
    {
        return "{$this->dir}/{$this->getViewPath()}";
    }

    /**
     * @inheritDoc
     */
    protected function getViewPath(): string
    {
        return isset($this->view['domain'])
            ? "{$this->getFolderPath()}{$this->view['domain']}/{$this->getEntry()}/{$this->getFolder()}/"
            : "{$this->getFolderPath()}{$this->getFolder()}/";
    }

    /**
     * @inheritDoc
     */
    protected function getLocateFolder(): string
    {
        return $this->locate;
    }

    /**
     * @inheritDoc
     */
    protected function getHook(): string
    {
        if (isset($this->hook)) {
            return $this->hook;
        }

        if (!isset(static::$cache['hook'])) {
            static::$cache['hook'] = \str_replace('-', '_', $this->getDirname());
        }

        return static::$cache['hook'];
    }

    /**
     * @inheritDoc
     */
    protected function getDirname(): string
    {
        if (!isset(static::$cache['dirname'])) {
            static::$cache['dirname'] = \basename($this->dir);
        }

        return static::$cache['dirname'];
    }

    /**
     * Get path to view folder.
     *
     * @return string
     */
    protected function getFolderPath(): string
    {
        return isset($this->path) ? "{$this->path}/" : '';
    }

    /**
     * Get view folder.
     *
     * @return string
     */
    protected function getFolder(): string
    {
        return $this->folder ?? 'views';
    }

    /**
     * Get entry from domain folder.
     *
     * @return string
     */
    protected function getEntry(): string
    {
        return $this->entry ?? 'Static';
    }
}
