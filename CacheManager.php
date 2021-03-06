<?php

namespace Driebit\HttpCacheBundle;

use Driebit\HttpCacheBundle\HttpCache\HttpCacheInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Manages HTTP cache invalidations
 *
 */
class CacheManager
{
    /**
     * @var HttpCacheInterface
     */
    protected $cache;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Invalidation queue
     *
     * @var array
     */
    protected $invalidationQueue = array();

    /**
     * Constructor
     *
     * @param HttpCacheInterface $cache  HTTP cache
     * @param RouterInterface    $router Symfony router
     */
    public function __construct(HttpCacheInterface $cache, RouterInterface $router)
    {
        $this->cache = $cache;
        $this->router = $router;
    }

    /**
     * Invalidate a path (URL)
     *
     * @param string $path Path
     *
     * @return $this
     */
    public function invalidatePath($path)
    {
        $this->invalidationQueue[$path] = $path;

        return $this;
    }

    /**
     * Invalidate a route
     *
     * @param string $name       Route name
     * @param array  $parameters Route parameters (optional)
     *
     * @return $this
     */
    public function invalidateRoute($name, $parameters = array())
    {
        $this->invalidatePath($this->router->generate($name, $parameters));

        return $this;
    }

    /**
     * Flush all paths queued for invalidation
     *
     * @return array Paths that were flushed from the queue
     */
    public function flush()
    {
        $queue = $this->getInvalidationQueue();

        if (0 === count($queue)) {
            return $queue;
        }

        $this->cache->invalidateUrls($queue);
        $this->invalidationQueue = array();

        return $queue;
    }

    /**
     * Get paths (URLs) that are queued for invalidation
     *
     * @return array
     */
    public function getInvalidationQueue()
    {
        return \array_values($this->invalidationQueue);
    }
}