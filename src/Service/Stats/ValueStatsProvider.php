<?php
namespace Brander\Bundle\EAVBundle\Service\Stats;

use Brander\Bundle\EAVBundle\Repo\Value;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * @author tomfun
 */
class ValueStatsProvider implements ProviderInterface
{
    const VALUE_STAT = 'Brander_Bundle_EAVBundle_Repo_ValueMinMax_';
    /**
     * @var \Closure
     */
    protected $createCacheItem;
    /**
     * @var Value
     */
    private $repoValue;

    /**
     * @param Value $value
     */
    public function __construct(Value $value)
    {
        $this->repoValue = $value;
    }

    /**
     * @param int $attributeId
     * @return string
     */
    static public function constructValueMinMaxKey($attributeId)
    {
        return self::VALUE_STAT.$attributeId;
    }

    /**
     * @param string $key
     * @return int
     */
    static public function parseValueMinMaxAttributeId($key)
    {
        $attributeId = str_replace(self::VALUE_STAT, '', $key);
        $int = (int) $attributeId;
        if ($attributeId != $int) {
            throw new InvalidArgumentException('Wrong attribute');
        }

        return $int;
    }

    /**
     * @param string $key
     * @return bool
     */
    static public function isValueMinMaxKey($key)
    {
        $attributeId = str_replace(self::VALUE_STAT, '', $key);
        $int = (string) (int) $attributeId;

        return $attributeId === $int;
    }

    /**
     * @param string $key
     * @return \float[]
     * @throws \Exception
     */
    public function getAttributeMinMax($key)
    {
        $attributeId = self::parseValueMinMaxAttributeId($key);

        return $this->repoValue->minMaxByAttributeId((int) $attributeId);
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
        if (strpos($key, self::VALUE_STAT) !== 0) {
            throw new InvalidArgumentException('Wrong attribute');
        }

        $item = new CacheItem();
        $item->set($this->getAttributeMinMax($key));

        return $item;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *    The key for which to check existence.
     *
     * @throws InvalidArgumentExceptionInterface
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        return self::isValueMinMaxKey($key);
    }
}