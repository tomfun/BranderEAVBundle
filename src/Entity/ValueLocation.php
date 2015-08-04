<?php
namespace Brander\Bundle\EAVBundle\Entity;

use Brander\Bundle\EAVBundle\Model\GeoLocation;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
        $this->value = (string)$this->valueTyped;
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
        return $this->valueTyped;
    }

}
