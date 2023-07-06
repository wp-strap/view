<?php

/**
 * Responsible getting and locating the view.
 *
 * @package WPStrap\Libs
 */

declare(strict_types=1);

namespace WPStrap\View;

/**
 * Class AbstractView
 */
abstract class AbstractView
{
    /**
     * Internal use only:
     *
     * @var array<int|string, string|string[]>
     */
    protected static array $cache = [];

    /**
     * The view.
     *
     * @var array<string, string|array<string, mixed>>
     */
    protected array $view;

    /**
     * Renders the view to string.
     *
     * @return string|void
     */
    public function __toString()
    {
        if (!isset($this->view['slug'])) {
            \wp_die('[View] No view has been set yet.');
        }

        try {
            return $this->getView();
        } catch (ViewException $e) {
            if (\defined('WP_DEBUG') && \WP_DEBUG) {
                \wp_die(\esc_html($e->getMessage()));
            } else {
                if (\defined('WP_DEBUG_LOG') && \WP_DEBUG_LOG) {
                    \error_log(\esc_html($e->getMessage())); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                }

                return ''; // A broken view should not break the site in production.
            }
        }
    }

    /**
     * Returns the view.
     *
     * @return string The view filename if one is located.
     * @throws ViewException
     */
    protected function getView(): string
    {
        // View file(s) to search for, in order.
        $fileNames = $this->getFileNames();

        // If the key is in the cache array, we've already located this file otherwise locate it.
        $located = static::$cache['views'][$fileNames[0]] ?? $this->locateView($fileNames, $fileNames[0]);


        if ($located) {
            \ob_start();
            \load_template($located, false, $this->view['args'] ?? []);

            return \ob_get_clean();
        }

        if ($located === false) {
            throw new ViewException(
                \sprintf(
                /* translators: %s is replaced with the view path. */
                    \esc_html__('Unable to locate view: %s'),
                    "{$this->getViewPath()}{$this->getViewFile()}"
                )
            );
        }

        return $located;
    }

    /**
     * Retrieve the name of the highest priority view file that exists.
     *
     * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
     * inherit from a parent theme can just overload one file. If the view is
     * not found in either of those, it looks in the theme-compat folder last.
     *
     * @param string|string[] $fileNames View file(s) to search for, in order.
     * @param string $cacheKey View cache key.
     *
     * @return false|string
     */
    protected function locateView($fileNames, string $cacheKey)
    {
        // If locate is turned off then just return main view.
        if (!isset($this->locate)) {
            return static::$cache['views'][$cacheKey] = "{$this->getAbsoluteViewPath()}/{$this->getViewFile()}";
        }

        // No file found yet.
        $located = false;

        // Remove empty entries.
        $fileNames = \array_filter((array)$fileNames);
        $filePaths = $this->getPaths();

        // Try to find a view file.
        foreach ($fileNames as $fileName) {
            // Trim off any slashes from the view name.
            $fileName = \ltrim($fileName, '/');

            // Try locating this view file by looping through the view paths.
            foreach ($filePaths as $filePath) {
                if (\file_exists($filePath . $fileName)) {
                    $located = $filePath . $fileName;
                    // Store the view path in the cache.
                    static::$cache['views'][$cacheKey] = $located;
                    break 2;
                }
            }
        }

        return $located;
    }

    /**
     * Given a slug and optional name, create the file names of views.
     *
     * @return string[]
     */
    protected function getFileNames(): array
    {
        return \apply_filters("{$this->getHook()}_render_view", [$this->getViewFile()], $this->view['slug'], $this->view['domain'] ?? null, $this->view['args'] ?? []);
    }

    /**
     * Return a list of paths to check for view locations.
     *
     * Default is to check in a child theme (if relevant) before a parent theme, so that themes which inherit from a
     * parent theme can just overload one file. If the view is not found in either of those, it looks in the
     * theme-compat folder last.
     *
     * @return array<int, string>
     */
    protected function getPaths(): array
    {
        $themeDirectory = \trailingslashit($this->getLocateFolder());

        $filePaths = [
            10 => \trailingslashit(\get_template_directory()) . $themeDirectory,
            100 => $this->getAbsoluteViewPath()
        ];

        // Only add this conditionally, so non-child themes don't redundantly check active theme twice.
        if (\get_stylesheet_directory() !== \get_template_directory()) {
            $filePaths[1] = \trailingslashit(\get_stylesheet_directory()) . $themeDirectory;
        }

        /**
         * Allow ordered list of view paths to be amended.
         *
         * @param array $filePaths Default is directory in child theme at index 1, parent theme at 10, and config at 100.
         */
        $filePaths = \apply_filters("{$this->getHook()}_view_paths", $filePaths);

        // Sort the file paths based on priority.
        \ksort($filePaths, \SORT_NUMERIC);

        return \array_map('\trailingslashit', $filePaths);
    }

    /**
     * Get the view file.
     *
     * @return string
     */
    abstract protected function getViewFile(): string;

    /**
     * Returns the absolute path to view file(s).
     *
     * @return string
     */
    abstract protected function getAbsoluteViewPath(): string;

    /**
     * Get path to view file(s).
     *
     * @return string
     */
    abstract protected function getViewPath(): string;

    /**
     * Get the folder to locate in theme directories.
     *
     * @return string
     */
    abstract protected function getLocateFolder(): string;

    /**
     * Get a unique project hook prefix based on the project folder.
     *
     * @return string
     */
    abstract protected function getHook(): string;

    /**
     * Get the project folder name
     *
     * @return string
     */
    abstract protected function getDirname(): string;
}
