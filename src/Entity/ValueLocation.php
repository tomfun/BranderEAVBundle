<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Brander\Bundle\EAVBundle\Model\GeoLocation;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Значение аттрибута для положения на карте
 *
 * @author tomfun
 *
 * @ORM\Entity()
 * @Serializer\ExclusionPolicy("all")
 */
class ValueLocation extends Value
{
    const ELASTICA_POSTFIX = '_eavpnt_geo';
    /**
     * @Serializer\Accessor(getter="getValue",setter="setValue")
     * @Serializer\Type("Brander\Bundle\EAVBundle\Model\GeoLocation")
     * @Assert\Regex(pattern="/^-?\d+([.]\d+)?,-?\d+([.]\d+)?$/", message="must be a valid number")
     */
    protected $value;
    /**
     * @Assert\Valid()
     * @var GeoLocation
     */
    protected $valueTyped = null;

    /**
     * @param GeoLocation|string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->valueTyped = $value instanceof GeoLocation ? $value : new GeoLocation($value);
        $this->value = (string) $this->valueTyped;

        return $this;
    }

    /**
     * @return GeoLocation|null
     */
    public function getValue()
    {
        if (!$this->valueTyped) {
            if ($this->value) {
                $this->valueTyped = new GeoLocation($this->value);
            }
        }

//        return '#######';
        return $this->valueTyped;
    }
}

