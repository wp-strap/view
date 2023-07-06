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
     * Global view args.
     *
     * @var array<string|int, mixed>
     */
    protected array $args;

    /**
     * Set configurations.
     *
     * @param array<string, string|array<string|int, string|string[]>> $config
     *
     * @return self
     */
    public function register(array $config): self
    {
        foreach (['dir', 'hook', 'folder', 'path', 'entry', 'locate', 'args'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = \is_string($config[$key]) ? \untrailingslashit($config[$key]) : $config[$key];
            }
        }

        return $this;
    }

    /**
     * Defines the view that we want to render.
     *
     * @param string ...$paths
     *
     * @return $this
     */
    public function render(string ...$paths): self
    {
        if (empty($paths[1])) {
            $this->view = ['slug' => $paths[0]];
        } else {
            $this->view = [
                'domain' => $paths[0],
                'slug'   => $paths[1]
            ];
        }

        return $this;
    }

    /**
     * Defines the args that we want to parse into the view.
     *
     * @param array<string|int, mixed> $args
     *
     * @return $this
     */
    public function args(array $args): self
    {
        if (! isset($this->view['slug'])) {
            \wp_die('[View] args() shouldn\'t be called before render().');
        }

        $this->view['args'] = isset($this->args) ? \array_merge($this->args, $args) : $args;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getViewFile(): string
    {
        return $this->view['slug'] . '.php';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAbsoluteViewPath(): string
    {
        return "{$this->dir}/{$this->getViewPath()}";
    }

    /**
     * {@inheritdoc}
     */
    protected function getViewPath(): string
    {
        return isset($this->view['domain'])
            ? "{$this->getFolderPath()}{$this->view['domain']}/{$this->getEntry()}/{$this->getFolder()}/"
            : "{$this->getFolderPath()}{$this->getFolder()}/";
    }

    /**
     * {@inheritdoc}
     */
    protected function getLocateFolder(): string
    {
        return $this->locate;
    }

    /**
     * {@inheritdoc}
     */
    protected function getHook(): string
    {
        if (isset($this->hook)) {
            return $this->hook;
        }

        if (! isset(static::$cache['hook'])) {
            static::$cache['hook'] = \str_replace('-', '_', $this->getDirname());
        }

        return static::$cache['hook'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDirname(): string
    {
        if (! isset(static::$cache['dirname'])) {
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
