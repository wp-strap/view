<?php

/**
 * The views' facade
 *
 * @package WPStrap\Libs
 */

declare(strict_types=1);

namespace WPStrap\View;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Class View
 *
 * @method static ViewInterface register(array $config)
 * @method static ViewInterface render(string ...$paths)
 * @method static ViewInterface args(array $args)
 */
class View
{
    /**
     * The View.
     *
     * @var ViewInterface
     */
    protected static ViewInterface $view;

    /**
     * PSR Container.
     *
     * @var ContainerInterface
     */
    protected static ContainerInterface $container;

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array<string|int, mixed> $args
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::resolveInstance();

        if (! isset($instance)) {
            throw new RuntimeException('[Views] View service could not be resolved.');
        }

        return $instance->{$method}(...$args);
    }

    /**
     * Resolve the facade instance.
     *
     * @return ViewInterface|null
     */
    protected static function resolveInstance(): ?ViewInterface
    {
        if (! isset(static::$view) && ! isset(static::$container)) {
            static::$view = new ViewService();
        }

        return static::$view;
    }

    /**
     * Set facade(s).
     *
     * @param ViewInterface ...$instances
     *
     * @return void
     */
    public static function setFacade(...$instances)
    {
        foreach ($instances as $instance) {
            if ($instance instanceof ViewInterface) {
                static::$view = $instance;
            }
        }
    }

    /**
     * Set facade accessor.
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    public static function setFacadeAccessor(ContainerInterface $container)
    {
        static::$container = $container;

        if (static::$container->has(ViewInterface::class)) {
            static::setFacade(static::resolveFacadeAccessor(ViewInterface::class));
        }
    }

    /**
     * Get the registered class from the container.
     *
     * @param string $id
     *
     * @return mixed|void
     */
    protected static function resolveFacadeAccessor(string $id)
    {
        try {
            return static::$container->get($id);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            if (\defined('WP_DEBUG') && \WP_DEBUG) {
                \wp_die(\esc_html($e->getMessage()));
            }
        }
    }
}
