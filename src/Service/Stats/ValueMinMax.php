<?php
namespace Brander\Bundle\EAVBundle\Service\Filter;

use Brander\Bundle\EAVBundle\Repo\Value;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;


/**
 * Span for stats data
 *
 * @author Tomfun <tomfun1990@gmail.com>
 */
class ValueMinMax implements CacheItemInterface
{
    const VALUE_STAT = 'Brander\Bundle\EAVBundle\Repo\ValueMinMax';
    /**
     * @var int
     */
    private $id;
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var Value
     */
    private $repoValue;

    /**
     * @param Value $value
     * @param int   $id
     */
    public function __construct(Value $value, $id)
    {
        $this->repoValue = $value;
        $this->id = $id;
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
    static public function parseAttributeId($key)
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
    static public function isMyKey($key)
    {
        $attributeId = str_replace(self::VALUE_STAT, '', $key);
        $int = (int) $attributeId;

        return $attributeId === $int;
    }

    /**
     * @param string $key
     * @return \float[]
     * @throws \Exception
     */
    public function getAttributeMinMax($key)
    {
        $attributeId = self::parseAttributeId($key);

        return $this->repoValue->minMaxByAttributeId((int) $attributeId);
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return self::constructValueMinMaxKey($this->id);
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return float[]
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        if ($this->data) {
            return $this->data;
        }
        return $this->data = $this->getAttributeMinMax($this->getKey());
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        return isset($this->data);
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param float[] $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        $this->data = $value;

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        // TODO: Implement expiresAt() method.
        var_dump('1111111');
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        // TODO: Implement expiresAfter() method.
        var_dump('111111133333333');
    }
}