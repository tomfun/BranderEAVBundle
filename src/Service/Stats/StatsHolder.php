<?php
namespace Brander\Bundle\EAVBundle\Service\Filter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * Service to work with cached data items
 *
 * @author Tomfun <tomfun1990@gmail.com>
 */
class StatsHolder
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache = [];
    /**
     * @var CacheItemPoolInterface[]
     */
    private $providers = [];

    /**
     * StatsHolder constructor.
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param CacheItemPoolInterface $provider
     */
    public function addProvider(CacheItemPoolInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentExceptionInterface
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        if ($this->cache->hasItem($key)) {
            $this->cache->getItem($key);
        }
        foreach ($this->providers as $provider) {
            if ($provider->hasItem($key)) {
                $item = $provider->getItem($key);
                $this->cache->saveDeferred($item);
                return $item;
            }
        }
        throw new InvalidArgumentException();
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentExceptionInterface
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = [])
    {
        try {
            if ($items = $this->getItems($keys)) {
                return $items;
            }
        } catch (InvalidArgumentExceptionInterface $e) {
            // swallow
        }
        $items = [];
        foreach ($keys as $key) {
            $items[] = $this->getItem($key);
        }
        return $items;
    }
}