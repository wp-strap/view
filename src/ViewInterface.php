<?php

/**
 * Contract for the View.
 *
 * @package WPStrap\View
 */

declare(strict_types=1);

namespace WPStrap\View;

/**
 * Class ViewInterface
 */
interface ViewInterface
{
    /**
     * Set configurations.
     *
     * @param array<string, string|array<string|int, string|string[]>> $config
     *
     * @return self
     */
    public function register(array $config): self;

    /**
     * Defines the view that we want to render.
     *
     * @param string ...$paths
     *
     * @return $this
     */
    public function render(string ...$paths): self;

    /**
     * Defines the args that we want to parse into the view.
     *
     * @param array<string|int, mixed> $args
     *
     * @return $this
     */
    public function args(array $args): self;
}
